<?php
/**
 * GitHub repository activity fetcher with local file cache.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class GitHub_Repository_Activity_Service {

    const CACHE_SUBDIR = 'qiling/github-activity';
    const CACHE_TTL    = 21600;
    const RETRY_TTL    = 900;
    const LOCK_TTL     = 120;

    /**
     * Collect local file cache stats.
     *
     * @return array<string,mixed>
     */
    public function collect_cache_stats() {
        $dir   = $this->get_cache_dir( false );
        $count = 0;
        $bytes = 0;

        if ( '' !== $dir && is_dir( $dir ) ) {
            try {
                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator( $dir, \FilesystemIterator::SKIP_DOTS )
                );
                foreach ( $iterator as $item ) {
                    if ( $item->isFile() && $this->is_cache_payload_file( $item->getPathname() ) ) {
                        $count++;
                        $bytes += (int) $item->getSize();
                    }
                }
            } catch ( \Exception $e ) {
                // Return the partial stats collected so far.
            }
        }

        return array(
            'count'      => $count,
            'bytes'      => $bytes,
            'size_human' => function_exists( 'size_format' ) ? size_format( $bytes, 2 ) : ( $bytes . ' B' ),
            'dir'        => $this->get_expected_cache_dir(),
        );
    }

    /**
     * Clear local file cache.
     *
     * @return array<string,int>
     */
    public function clear_cache_files() {
        $dir           = $this->get_cache_dir( false );
        $deleted_files = 0;
        $freed_bytes   = 0;
        $failed_files  = 0;

        if ( '' === $dir || ! is_dir( $dir ) ) {
            return array(
                'deleted_files' => 0,
                'freed_bytes'   => 0,
                'failed_files'  => 0,
            );
        }

        $allowed_roots = array_filter(
            array(
                function_exists( 'developer_starter_filesystem_upload_basedir' ) ? developer_starter_filesystem_upload_basedir() : $this->get_upload_basedir(),
                $dir,
            )
        );

        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator( $dir, \FilesystemIterator::SKIP_DOTS ),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ( $iterator as $item ) {
                $path = $item->getPathname();
                if ( $item->isFile() ) {
                    if ( ! $this->is_cache_payload_file( $path ) ) {
                        continue;
                    }

                    $size = (int) $item->getSize();
                    $deleted = function_exists( 'developer_starter_filesystem_delete_file' )
                        ? developer_starter_filesystem_delete_file(
                            $path,
                            array(
                                'operation'     => 'delete_github_activity_cache_file',
                                'allowed_roots' => $allowed_roots,
                                'context'       => array( 'component' => 'github_activity_cache' ),
                            )
                        )
                        : false;

                    if ( $deleted ) {
                        $deleted_files++;
                        $freed_bytes += $size;
                    } else {
                        $failed_files++;
                    }
                } elseif ( $item->isDir() && function_exists( 'developer_starter_filesystem_delete_empty_dir' ) ) {
                    developer_starter_filesystem_delete_empty_dir(
                        $path,
                        array(
                            'operation'     => 'delete_github_activity_cache_dir',
                            'allowed_roots' => $allowed_roots,
                            'context'       => array( 'component' => 'github_activity_cache' ),
                        )
                    );
                }
            }
        } catch ( \Exception $e ) {
            // Return the partial cleanup result.
        }

        return array(
            'deleted_files' => $deleted_files,
            'freed_bytes'   => $freed_bytes,
            'failed_files'  => $failed_files,
        );
    }

    /**
     * Get repository activity.
     *
     * @param string              $repository Repository URL or owner/repo.
     * @param array<string,mixed> $args       Fetch args.
     * @return array<string,mixed>
     */
    public function get_activity( $repository, $args = array() ) {
        $repo = $this->parse_repository( $repository );
        if ( is_wp_error( $repo ) ) {
            return $this->build_error_payload( $repo->get_error_message() );
        }

        $ttl          = $this->normalize_ttl( isset( $args['ttl'] ) ? $args['ttl'] : self::CACHE_TTL );
        $commit_count = isset( $args['commit_count'] ) ? absint( $args['commit_count'] ) : 5;
        $commit_count = max( 1, min( 10, $commit_count ) );
        $cache_key    = $this->get_cache_key( $repo );
        $cached       = $this->read_cache( $cache_key );
        $now          = time();

        if ( is_array( $cached ) && ! empty( $cached['expires_at'] ) && (int) $cached['expires_at'] > $now ) {
            $cached['cache_status'] = 'hit';
            $cached['stale']        = false;
            return $cached;
        }

        if (
            is_array( $cached )
            && ! empty( $cached['next_retry_at'] )
            && (int) $cached['next_retry_at'] > $now
            && $this->has_repository_payload( $cached )
        ) {
            $cached['cache_status'] = 'stale';
            $cached['stale']        = true;
            return $cached;
        }

        if ( ! $this->acquire_lock( $cache_key ) && $this->has_repository_payload( $cached ) ) {
            $cached['cache_status'] = 'locked';
            $cached['stale']        = true;
            return $cached;
        }

        $fresh = $this->fetch_activity( $repo, $commit_count );
        $this->release_lock( $cache_key );

        if ( ! is_wp_error( $fresh ) ) {
            $fresh['fetched_at']   = $now;
            $fresh['expires_at']   = $now + $ttl;
            $fresh['next_retry_at'] = 0;
            $fresh['cache_status'] = 'refresh';
            $fresh['stale']        = false;
            $this->write_cache( $cache_key, $fresh );
            return $fresh;
        }

        if ( $this->has_repository_payload( $cached ) ) {
            $cached['next_retry_at'] = $now + self::RETRY_TTL;
            $cached['cache_status']  = 'stale';
            $cached['stale']         = true;
            $cached['error']         = $fresh->get_error_message();
            $this->write_cache( $cache_key, $cached );
            return $cached;
        }

        $error_payload = $this->build_error_payload( $fresh->get_error_message(), $repo );
        $error_payload['expires_at']    = $now + self::RETRY_TTL;
        $error_payload['next_retry_at'] = $now + self::RETRY_TTL;
        $this->write_cache( $cache_key, $error_payload );

        return $error_payload;
    }

    /**
     * Parse GitHub repository URL.
     *
     * @param string $repository Repository URL or owner/repo.
     * @return array<string,string>|\WP_Error
     */
    public function parse_repository( $repository ) {
        $repository = trim( wp_strip_all_tags( (string) $repository ) );
        if ( '' === $repository ) {
            return new \WP_Error( 'empty_repository', __( '请先填写 GitHub 仓库地址。', 'developer-starter' ) );
        }

        $path = $repository;
        if ( false !== strpos( $repository, '://' ) ) {
            $host = (string) parse_url( $repository, PHP_URL_HOST );
            $path = (string) parse_url( $repository, PHP_URL_PATH );
            $host = strtolower( preg_replace( '/^www\./', '', $host ) );
            if ( 'github.com' !== $host ) {
                return new \WP_Error( 'invalid_repository_host', __( '仅支持 github.com 仓库地址。', 'developer-starter' ) );
            }
        }

        $path = trim( $path, " \t\n\r\0\x0B/" );
        $path = preg_replace( '/\.git$/i', '', $path );
        $parts = explode( '/', (string) $path );
        if ( count( $parts ) < 2 ) {
            return new \WP_Error( 'invalid_repository', __( '仓库地址格式应为 https://github.com/owner/repo。', 'developer-starter' ) );
        }

        $owner = sanitize_key( $parts[0] );
        $repo  = $this->sanitize_repo_segment( $parts[1] );

        if ( '' === $owner || '' === $repo ) {
            return new \WP_Error( 'invalid_repository_name', __( '仓库 owner 或 repo 名称无效。', 'developer-starter' ) );
        }

        return array(
            'owner'     => $owner,
            'repo'      => $repo,
            'full_name' => $owner . '/' . $repo,
            'html_url'  => 'https://github.com/' . rawurlencode( $owner ) . '/' . rawurlencode( $repo ),
        );
    }

    /**
     * Fetch activity from GitHub.
     *
     * @param array<string,string> $repo Repository data.
     * @param int                  $commit_count Commit count.
     * @return array<string,mixed>|\WP_Error
     */
    private function fetch_activity( $repo, $commit_count ) {
        $base = 'https://api.github.com/repos/' . rawurlencode( $repo['owner'] ) . '/' . rawurlencode( $repo['repo'] );

        $repository = $this->request_json( $base );
        if ( is_wp_error( $repository ) ) {
            return $repository;
        }

        $release = $this->request_json( $base . '/releases/latest', array( 404 ) );
        if ( is_wp_error( $release ) ) {
            $release = null;
        }

        $commits = $this->request_json( add_query_arg( 'per_page', $commit_count, $base . '/commits' ), array( 404, 409 ) );
        if ( is_wp_error( $commits ) || ! is_array( $commits ) ) {
            $commits = array();
        }

        return array(
            'repository'     => $this->normalize_repository( $repository, $repo ),
            'latest_release' => is_array( $release ) ? $this->normalize_release( $release ) : null,
            'commits'        => $this->normalize_commits( $commits ),
            'error'          => '',
        );
    }

    /**
     * Request JSON from GitHub.
     *
     * @param string $url Allowed GitHub API URL.
     * @param int[]  $soft_statuses Status codes treated as empty responses.
     * @return mixed|\WP_Error
     */
    private function request_json( $url, $soft_statuses = array() ) {
        $response = wp_remote_get(
            $url,
            array(
                'timeout'     => 4,
                'redirection' => 2,
                'headers'     => array(
                    'Accept'               => 'application/vnd.github+json',
                    'User-Agent'           => 'Qiling-Theme-GitHub-Activity/' . ( defined( 'DEVELOPER_STARTER_VERSION' ) ? DEVELOPER_STARTER_VERSION : '1.0' ),
                    'X-GitHub-Api-Version' => '2022-11-28',
                ),
            )
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $status = (int) wp_remote_retrieve_response_code( $response );
        if ( in_array( $status, $soft_statuses, true ) ) {
            return array();
        }

        if ( $status < 200 || $status >= 300 ) {
            return new \WP_Error(
                'github_api_error',
                sprintf(
                    /* translators: %d: HTTP status code. */
                    __( 'GitHub API 请求失败，状态码：%d。', 'developer-starter' ),
                    $status
                )
            );
        }

        $body = json_decode( (string) wp_remote_retrieve_body( $response ), true );
        if ( JSON_ERROR_NONE !== json_last_error() ) {
            return new \WP_Error( 'github_json_error', __( 'GitHub API 返回数据解析失败。', 'developer-starter' ) );
        }

        return $body;
    }

    /**
     * @param array<string,mixed>  $data Raw repository data.
     * @param array<string,string> $repo Parsed repository data.
     * @return array<string,mixed>
     */
    private function normalize_repository( $data, $repo ) {
        $license = '';
        if ( isset( $data['license'] ) && is_array( $data['license'] ) && ! empty( $data['license']['spdx_id'] ) ) {
            $license = sanitize_text_field( (string) $data['license']['spdx_id'] );
        }

        return array(
            'owner'          => $repo['owner'],
            'name'           => $repo['repo'],
            'full_name'      => isset( $data['full_name'] ) ? sanitize_text_field( (string) $data['full_name'] ) : $repo['full_name'],
            'html_url'       => isset( $data['html_url'] ) ? esc_url_raw( (string) $data['html_url'] ) : $repo['html_url'],
            'description'    => isset( $data['description'] ) ? sanitize_text_field( (string) $data['description'] ) : '',
            'language'       => isset( $data['language'] ) ? sanitize_text_field( (string) $data['language'] ) : '',
            'license'        => $license,
            'stars'          => isset( $data['stargazers_count'] ) ? absint( $data['stargazers_count'] ) : 0,
            'forks'          => isset( $data['forks_count'] ) ? absint( $data['forks_count'] ) : 0,
            'open_issues'    => isset( $data['open_issues_count'] ) ? absint( $data['open_issues_count'] ) : 0,
            'default_branch' => isset( $data['default_branch'] ) ? sanitize_text_field( (string) $data['default_branch'] ) : '',
            'pushed_at'      => isset( $data['pushed_at'] ) ? sanitize_text_field( (string) $data['pushed_at'] ) : '',
        );
    }

    /**
     * @param array<string,mixed> $data Raw release data.
     * @return array<string,string>|null
     */
    private function normalize_release( $data ) {
        if ( empty( $data ) || empty( $data['html_url'] ) ) {
            return null;
        }

        return array(
            'tag_name'     => isset( $data['tag_name'] ) ? sanitize_text_field( (string) $data['tag_name'] ) : '',
            'name'         => isset( $data['name'] ) ? sanitize_text_field( (string) $data['name'] ) : '',
            'html_url'     => esc_url_raw( (string) $data['html_url'] ),
            'published_at' => isset( $data['published_at'] ) ? sanitize_text_field( (string) $data['published_at'] ) : '',
        );
    }

    /**
     * @param array<int,array<string,mixed>> $commits Raw commits.
     * @return array<int,array<string,string>>
     */
    private function normalize_commits( $commits ) {
        $items = array();
        foreach ( (array) $commits as $commit ) {
            if ( ! is_array( $commit ) ) {
                continue;
            }

            $commit_data = isset( $commit['commit'] ) && is_array( $commit['commit'] ) ? $commit['commit'] : array();
            $author_data = isset( $commit_data['author'] ) && is_array( $commit_data['author'] ) ? $commit_data['author'] : array();
            $message     = isset( $commit_data['message'] ) ? (string) $commit_data['message'] : '';
            $message     = preg_split( '/\r\n|\r|\n/', $message );
            $message     = is_array( $message ) && isset( $message[0] ) ? $message[0] : '';

            $items[] = array(
                'sha'      => isset( $commit['sha'] ) ? substr( sanitize_text_field( (string) $commit['sha'] ), 0, 7 ) : '',
                'message'  => sanitize_text_field( $message ),
                'author'   => isset( $author_data['name'] ) ? sanitize_text_field( (string) $author_data['name'] ) : '',
                'date'     => isset( $author_data['date'] ) ? sanitize_text_field( (string) $author_data['date'] ) : '',
                'html_url' => isset( $commit['html_url'] ) ? esc_url_raw( (string) $commit['html_url'] ) : '',
            );
        }

        return $items;
    }

    /**
     * @param string $message Error message.
     * @param array<string,string>|null $repo Repository data.
     * @return array<string,mixed>
     */
    private function build_error_payload( $message, $repo = null ) {
        return array(
            'repository'     => is_array( $repo ) ? array(
                'owner'     => $repo['owner'],
                'name'      => $repo['repo'],
                'full_name' => $repo['full_name'],
                'html_url'  => $repo['html_url'],
            ) : null,
            'latest_release' => null,
            'commits'        => array(),
            'fetched_at'     => 0,
            'expires_at'     => 0,
            'next_retry_at'  => 0,
            'cache_status'   => 'error',
            'stale'          => false,
            'error'          => sanitize_text_field( (string) $message ),
        );
    }

    /**
     * @param mixed $payload Payload.
     * @return bool
     */
    private function has_repository_payload( $payload ) {
        return is_array( $payload ) && ! empty( $payload['repository'] ) && is_array( $payload['repository'] );
    }

    /**
     * @param array<string,string> $repo Repository data.
     * @return string
     */
    private function get_cache_key( $repo ) {
        return 'repo-' . md5( strtolower( $repo['full_name'] ) );
    }

    /**
     * @param string $key Cache key.
     * @return array<string,mixed>|false
     */
    private function read_cache( $key ) {
        $file = $this->get_cache_file( $key );
        if ( '' === $file || ! is_readable( $file ) ) {
            return false;
        }

        $payload = json_decode( (string) file_get_contents( $file ), true );
        return is_array( $payload ) ? $payload : false;
    }

    /**
     * @param string              $key Cache key.
     * @param array<string,mixed> $payload Payload.
     * @return bool
     */
    private function write_cache( $key, $payload ) {
        $file = $this->get_cache_file( $key, true );
        if ( '' === $file ) {
            return false;
        }

        $json = wp_json_encode( $payload );
        if ( ! is_string( $json ) ) {
            return false;
        }

        if ( ! function_exists( 'developer_starter_filesystem_write_file' ) ) {
            return false;
        }

        return developer_starter_filesystem_write_file(
            $file,
            $json,
            $this->get_cache_filesystem_args( 'write_github_activity_cache_file' )
        );
    }

    /**
     * @param string $key Cache key.
     * @param bool   $create Whether to create cache dir.
     * @return string
     */
    private function get_cache_file( $key, $create = false ) {
        $dir = $this->get_cache_dir( $create );
        if ( '' === $dir || ! preg_match( '/^[A-Za-z0-9_-]+$/', $key ) ) {
            return '';
        }

        return trailingslashit( $dir ) . $key . '.json';
    }

    /**
     * @param bool $create Whether to create cache dir.
     * @return string
     */
    private function get_cache_dir( $create = false ) {
        $upload_dir = wp_upload_dir( null, false );
        if ( ! empty( $upload_dir['error'] ) || empty( $upload_dir['basedir'] ) ) {
            return '';
        }

        $dir = trailingslashit( $upload_dir['basedir'] ) . self::CACHE_SUBDIR;
        if ( $create && ! is_dir( $dir ) ) {
            wp_mkdir_p( $dir );
        }

        if ( ! is_dir( $dir ) ) {
            return '';
        }

        if ( $create && ! is_writable( $dir ) ) {
            return '';
        }

        $index = trailingslashit( $dir ) . 'index.html';
        if ( $create && ! file_exists( $index ) && function_exists( 'developer_starter_filesystem_write_file' ) ) {
            developer_starter_filesystem_write_file(
                $index,
                '',
                $this->get_cache_filesystem_args( 'write_github_activity_cache_index' )
            );
        }

        return $dir;
    }

    /**
     * @return string
     */
    private function get_expected_cache_dir() {
        $base_dir = $this->get_upload_basedir();
        return '' === $base_dir ? '' : trailingslashit( $base_dir ) . self::CACHE_SUBDIR;
    }

    /**
     * @return string
     */
    private function get_upload_basedir() {
        $upload_dir = wp_upload_dir( null, false );
        if ( ! empty( $upload_dir['error'] ) || empty( $upload_dir['basedir'] ) ) {
            return '';
        }

        return (string) $upload_dir['basedir'];
    }

    /**
     * @param string $path File path.
     * @return bool
     */
    private function is_cache_payload_file( $path ) {
        $path = (string) $path;
        if ( '' === $path || ! is_file( $path ) ) {
            return false;
        }

        $basename = basename( $path );
        return 'index.html' !== $basename && '.json' === strtolower( substr( $basename, -5 ) );
    }

    /**
     * @param string $key Cache key.
     * @return bool
     */
    private function acquire_lock( $key ) {
        $lock_file = $this->get_cache_file( $key . '-lock', true );
        if ( '' === $lock_file ) {
            return true;
        }

        if ( file_exists( $lock_file ) && ( time() - (int) filemtime( $lock_file ) ) < self::LOCK_TTL ) {
            return false;
        }

        if ( ! function_exists( 'developer_starter_filesystem_write_file' ) ) {
            return false;
        }

        return developer_starter_filesystem_write_file(
            $lock_file,
            (string) time(),
            $this->get_cache_filesystem_args( 'write_github_activity_cache_lock' )
        );
    }

    /**
     * @param string $key Cache key.
     * @return void
     */
    private function release_lock( $key ) {
        $lock_file = $this->get_cache_file( $key . '-lock' );
        if ( '' !== $lock_file && file_exists( $lock_file ) && function_exists( 'developer_starter_filesystem_delete_file' ) ) {
            developer_starter_filesystem_delete_file(
                $lock_file,
                $this->get_cache_filesystem_args( 'delete_github_activity_cache_lock' )
            );
        }
    }

    /**
     * Build guarded filesystem arguments for GitHub activity cache files.
     *
     * @param string $operation Operation id.
     * @return array<string,mixed>
     */
    private function get_cache_filesystem_args( $operation ) {
        return array(
            'operation'     => sanitize_key( (string) $operation ),
            'allowed_roots' => array_filter(
                array(
                    function_exists( 'developer_starter_filesystem_upload_basedir' ) ? developer_starter_filesystem_upload_basedir() : $this->get_upload_basedir(),
                    $this->get_expected_cache_dir(),
                )
            ),
            'create_parent' => true,
            'context'       => array( 'component' => 'github_activity_cache' ),
        );
    }

    /**
     * @param mixed $ttl TTL seconds.
     * @return int
     */
    private function normalize_ttl( $ttl ) {
        $ttl = absint( $ttl );
        if ( $ttl <= 0 ) {
            $ttl = self::CACHE_TTL;
        }

        return max( HOUR_IN_SECONDS, min( WEEK_IN_SECONDS, $ttl ) );
    }

    /**
     * @param string $value Repo segment.
     * @return string
     */
    private function sanitize_repo_segment( $value ) {
        $value = trim( (string) $value );
        return preg_match( '/^[A-Za-z0-9_.-]+$/', $value ) ? $value : '';
    }
}
