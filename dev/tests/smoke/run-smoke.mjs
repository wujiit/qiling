#!/usr/bin/env node

import * as aiDecorationSuite from './ai-decoration.smoke.mjs';
import * as aiLocalizationSuite from './ai-localization.smoke.mjs';
import * as authManagerSuite from './auth-manager.smoke.mjs';
import * as contentModelCenterSuite from './content-model-center.smoke.mjs';
import * as cookieConsentSuite from './cookie-consent.smoke.mjs';
import * as error404RedirectSuite from './error-404-redirect.smoke.mjs';
import * as designTokensSuite from './design-tokens.smoke.mjs';
import * as footerColumnsSuite from './footer-columns.smoke.mjs';
import * as frontendBuilderSuite from './frontend-builder.smoke.mjs';
import * as infiniteScrollSuite from './infinite-scroll.smoke.mjs';
import * as moduleStandardsSuite from './module-standards.smoke.mjs';
import * as multilingualSeoProviderSuite from './multilingual-seo-provider.smoke.mjs';
import * as pagePackageSuite from './page-package.smoke.mjs';
import * as performanceA11ySuite from './performance-a11y.smoke.mjs';
import * as runtimeSafetySuite from './runtime-safety.smoke.mjs';
import * as schemaPreviewSuite from './schema-preview.smoke.mjs';
import * as seoPushSuite from './seo-push.smoke.mjs';
import * as seoSchemaEngineSuite from './seo-schema-engine.smoke.mjs';
import * as settingsDefaultsSuite from './settings-defaults.smoke.mjs';
import * as templateCenterSuite from './template-center.smoke.mjs';

const suites = [
  aiDecorationSuite,
  aiLocalizationSuite,
  authManagerSuite,
  contentModelCenterSuite,
  cookieConsentSuite,
  error404RedirectSuite,
  designTokensSuite,
  footerColumnsSuite,
  frontendBuilderSuite,
  infiniteScrollSuite,
  moduleStandardsSuite,
  multilingualSeoProviderSuite,
  pagePackageSuite,
  performanceA11ySuite,
  runtimeSafetySuite,
  schemaPreviewSuite,
  seoPushSuite,
  seoSchemaEngineSuite,
  settingsDefaultsSuite,
  templateCenterSuite,
];

const failures = [];

for (const suite of suites) {
  const suiteName = suite.name || 'Unnamed smoke suite';

  try {
    await suite.run();
    console.log(`PASS ${suiteName}`);
  } catch (error) {
    failures.push({
      name: suiteName,
      message: error instanceof Error ? error.message : String(error),
    });
    console.error(`FAIL ${suiteName}`);
    console.error(failures[failures.length - 1].message);
  }
}

if (failures.length > 0) {
  console.error(`Smoke test failures: ${failures.length}`);
  process.exit(1);
}

console.log(`Smoke test suites passed: ${suites.length}`);
