<?php
/**
 * Theme contact form helpers.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'developer_starter_get_explicit_contact_form_id' ) ) {
    /**
     * 获取主题中显式配置的启灵表单 ID。
     *
     * 留空时表示使用主题内置留言表单。
     *
     * @return int
     */
    function developer_starter_get_explicit_contact_form_id() {
        if ( ! function_exists( 'developer_starter_get_option' ) ) {
            return 0;
        }

        return absint( developer_starter_get_option( 'contact_form_id', 0 ) );
    }
}

if ( ! function_exists( 'developer_starter_get_builtin_contact_form_markup' ) ) {
    /**
     * 获取主题内置联系表单 HTML。
     *
     * @param array $args 表单参数。
     * @return string
     */
    function developer_starter_get_builtin_contact_form_markup( $args = array() ) {
        $defaults = array(
            'form_id'      => 'contact-form',
            'submit_text'  => __( '提交留言', 'developer-starter' ),
            'submit_class' => 'btn-submit',
            'message_rows' => 5,
            'extra_class'  => '',
        );

        $args = wp_parse_args( $args, $defaults );

        $form_id      = trim( (string) $args['form_id'] );
        $submit_text  = trim( (string) $args['submit_text'] );
        $submit_class = trim( (string) $args['submit_class'] );
        $message_rows = max( 3, absint( $args['message_rows'] ) );
        $extra_class  = trim( (string) $args['extra_class'] );
        $class_names  = array( 'contact-form' );

        if ( '' !== $extra_class ) {
            $class_names = array_merge( $class_names, preg_split( '/\s+/', $extra_class ) );
        }

        $class_names = array_values(
            array_unique(
                array_filter(
                    array_map( 'sanitize_html_class', $class_names )
                )
            )
        );

        if ( '' === $form_id ) {
            $form_id = 'contact-form';
        }

        if ( '' === $submit_text ) {
            $submit_text = __( '提交留言', 'developer-starter' );
        }
        $submit_classes = array_values( array_unique( array_filter( array_map( 'sanitize_html_class', preg_split( '/\s+/', $submit_class ) ) ) ) );
        if ( empty( $submit_classes ) ) {
            $submit_classes = array( 'btn-submit' );
        }

        ob_start();
        ?>
        <form
            id="<?php echo esc_attr( $form_id ); ?>"
            class="<?php echo esc_attr( implode( ' ', $class_names ) ); ?>"
            method="post"
            data-ds-message-form="1"
            novalidate
        >
            <input type="hidden" name="action" value="ds_submit_message" />
            <input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'ds_message_nonce' ) ); ?>" />

            <div class="form-row form-row-2">
                <div class="form-group">
                    <input
                        type="text"
                        name="name"
                        placeholder="<?php echo esc_attr__( '您的姓名 *', 'developer-starter' ); ?>"
                        required
                    />
                </div>

                <div class="form-group">
                    <input
                        type="tel"
                        name="phone"
                        placeholder="<?php echo esc_attr__( '联系电话', 'developer-starter' ); ?>"
                    />
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <input
                        type="email"
                        name="email"
                        placeholder="<?php echo esc_attr__( '电子邮箱', 'developer-starter' ); ?>"
                    />
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <textarea
                        name="message"
                        rows="<?php echo esc_attr( $message_rows ); ?>"
                        placeholder="<?php echo esc_attr__( '请输入您的留言内容 *', 'developer-starter' ); ?>"
                        required
                    ></textarea>
                </div>
            </div>

            <div class="form-message" aria-live="polite"></div>

            <button type="submit" class="<?php echo esc_attr( implode( ' ', $submit_classes ) ); ?>"><?php echo esc_html( $submit_text ); ?></button>
        </form>
        <?php

        return trim( ob_get_clean() );
    }
}

if ( ! function_exists( 'developer_starter_render_builtin_contact_form' ) ) {
    /**
     * 输出主题内置联系表单。
     *
     * @param array $args 表单参数。
     * @return void
     */
    function developer_starter_render_builtin_contact_form( $args = array() ) {
        echo developer_starter_get_builtin_contact_form_markup( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}
