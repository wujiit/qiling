import {
  assertContains,
  assertFileExists,
  readThemeFile,
} from './_helpers.mjs';

export const name = 'Archive infinite scroll contracts';

export async function run() {
  [
    'assets/js/infinite-scroll.js',
    'assets/css/infinite-scroll.css',
  ].forEach((file) => {
    assertFileExists(file, `Infinite scroll asset missing: ${file}`);
  });

  const configPhp = readThemeFile('inc/admin/traits/class-admin-settings-config-trait.php');
  [
    "'archive_loading_mode'",
    '分类/搜索页加载模式',
    '常规分页（默认）',
    '无限滚动',
  ].forEach((needle) => {
    assertContains(configPhp, needle, `Infinite scroll admin setting changed unexpectedly: ${needle}`);
  });

  const sanitizePhp = readThemeFile('inc/admin/traits/class-admin-settings-sanitize-trait.php');
  [
    "'archive_loading_mode'",
    "array( 'regular', 'infinite' )",
  ].forEach((needle) => {
    assertContains(sanitizePhp, needle, `Infinite scroll sanitize contract changed unexpectedly: ${needle}`);
  });

  const categoryPhp = readThemeFile('category.php');
  [
    'developer-starter-infinite-scroll',
    'data-qiling-infinite-scroll="1"',
    'data-context="category"',
    'data-adv-filter',
    'qiling-infinite-pagination-fallback',
    'qilingCategoryAdvancedFilterState',
    "formData.append('paged', '1')",
    'QilingInfiniteScroll.refresh',
  ].forEach((needle) => {
    assertContains(categoryPhp, needle, `Category infinite scroll contract changed unexpectedly: ${needle}`);
  });

  const advFilterPhp = readThemeFile('inc/core/helpers/helpers-advanced-category-filter.php');
  [
    "$paged",
    "'paged'          => $paged",
    "'needs_pagination' => true",
    "'max_num_pages' => (int) $query->max_num_pages",
    "'has_more'      => $paged < (int) $query->max_num_pages",
    "RAND(' . max( 1, $random_seed ) . ')",
  ].forEach((needle) => {
    assertContains(advFilterPhp, needle, `Advanced category filter pagination contract changed unexpectedly: ${needle}`);
  });

  const searchPhp = readThemeFile('search.php');
  [
    'developer-starter-infinite-scroll',
    'data-context="search"',
    'data-item-container=".search-results-list"',
    'search-results-pagination',
    'qiling-infinite-pagination-fallback',
  ].forEach((needle) => {
    assertContains(searchPhp, needle, `Search infinite scroll contract changed unexpectedly: ${needle}`);
  });

  const js = readThemeFile('assets/js/infinite-scroll.js');
  [
    'IntersectionObserver',
    'loadByAdvancedFilter',
    'loadByUrl',
    'ds_adv_category_filter',
    'qiling:infinite-scroll:loaded',
    'window.QilingInfiniteScroll',
  ].forEach((needle) => {
    assertContains(js, needle, `Infinite scroll JavaScript contract changed unexpectedly: ${needle}`);
  });
}
