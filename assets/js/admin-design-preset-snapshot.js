(function (window) {
    "use strict";

    function hasSnapshotValue(value) {
        return !(value === null || value === undefined || String(value).trim() === "");
    }

    function safeParseJsonObject(value) {
        if (!value) {
            return {};
        }
        try {
            var parsed = JSON.parse(value);
            return parsed && typeof parsed === "object" && !Array.isArray(parsed) ? parsed : {};
        } catch (error) {
            return {};
        }
    }

    function countValues(value) {
        if (Array.isArray(value)) {
            return value.reduce(function (total, item) {
                return total + countValues(item);
            }, 0);
        }
        if (!value || typeof value !== "object") {
            return hasSnapshotValue(value) ? 1 : 0;
        }
        return Object.keys(value).reduce(function (total, key) {
            return total + countValues(value[key]);
        }, 0);
    }

    function normalizeMessages(messages) {
        return messages && typeof messages === "object" ? messages : {};
    }

    function normalizeSnapshot(snapshot) {
        snapshot = snapshot && typeof snapshot === "object" ? snapshot : {};
        return {
            typographySystem: snapshot.typographySystem && typeof snapshot.typographySystem === "object" ? snapshot.typographySystem : {},
            layoutSystem: snapshot.layoutSystem && typeof snapshot.layoutSystem === "object" ? snapshot.layoutSystem : {},
            componentStyles: snapshot.componentStyles && typeof snapshot.componentStyles === "object" ? snapshot.componentStyles : {}
        };
    }

    function buildSummary(snapshot, messages) {
        var normalized = normalizeSnapshot(snapshot);
        var safeMessages = normalizeMessages(messages);
        var typographyCount = countValues(normalized.typographySystem);
        var layoutCount = countValues(normalized.layoutSystem);
        var componentCount = countValues(normalized.componentStyles);
        var template = safeMessages.snapshotSummary || "排版 %1$d · 布局 %2$d · 组件 %3$d";

        return {
            typography: typographyCount,
            layout: layoutCount,
            components: componentCount,
            text: template
                .replace("%1$d", typographyCount)
                .replace("%2$d", layoutCount)
                .replace("%3$d", componentCount)
        };
    }

    function readSnapshot(card) {
        if (!card) {
            return normalizeSnapshot({});
        }

        return normalizeSnapshot({
            typographySystem: safeParseJsonObject((card.querySelector("[data-design-preset-json='typography']") || {}).value || ""),
            layoutSystem: safeParseJsonObject((card.querySelector("[data-design-preset-json='layout']") || {}).value || ""),
            componentStyles: safeParseJsonObject((card.querySelector("[data-design-preset-json='components']") || {}).value || "")
        });
    }

    function applySnapshotToCard(card, snapshot, messages) {
        if (!card) {
            return;
        }

        var safeMessages = normalizeMessages(messages);
        var normalized = normalizeSnapshot(snapshot);
        var typographyField = card.querySelector("[data-design-preset-json='typography']");
        var layoutField = card.querySelector("[data-design-preset-json='layout']");
        var componentsField = card.querySelector("[data-design-preset-json='components']");

        if (typographyField) {
            typographyField.value = JSON.stringify(normalized.typographySystem, null, 2);
        }
        if (layoutField) {
            layoutField.value = JSON.stringify(normalized.layoutSystem, null, 2);
        }
        if (componentsField) {
            componentsField.value = JSON.stringify(normalized.componentStyles, null, 2);
        }

        var summary = buildSummary(normalized, safeMessages);
        var summaryNode = card.querySelector("[data-design-preset-snapshot-summary='1']");
        var typographyBadge = card.querySelector("[data-design-preset-snapshot-badge='typography']");
        var layoutBadge = card.querySelector("[data-design-preset-snapshot-badge='layout']");
        var componentsBadge = card.querySelector("[data-design-preset-snapshot-badge='components']");

        if (summaryNode) {
            summaryNode.textContent = summary.typography || summary.layout || summary.components
                ? summary.text
                : (safeMessages.snapshotEmpty || "当前仅保存了颜色；可使用上方按钮保存完整效果。");
        }
        if (typographyBadge) {
            typographyBadge.textContent = "排版 " + summary.typography;
        }
        if (layoutBadge) {
            layoutBadge.textContent = "布局 " + summary.layout;
        }
        if (componentsBadge) {
            componentsBadge.textContent = "组件 " + summary.components;
        }
    }

    window.DSDesignPresetSnapshot = {
        safeParseJsonObject: safeParseJsonObject,
        countValues: countValues,
        buildSummary: buildSummary,
        readSnapshot: readSnapshot,
        applySnapshotToCard: applySnapshotToCard
    };
})(typeof window !== "undefined" ? window : {});
