/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/select',
    'uiRegistry',
    'underscore'
], function (Select, uiRegistry, _) {
    'use strict';

    return Select.extend({
        defaults: {
            imports: {
                relatedFieldPath: null
            },
            links: {
                value: null
            }
        },

        /** @inheritdoc */
        getInitialValue: function () {
            var values = [this.source.get(this.dataScope), this.default],
                value;

            values.some(function (v) {
                if (v !== null && v !== undefined) {
                    value = v;

                    return true;
                }

                return false;
            });

            return this.normalizeData(value);
        },

        /** @inheritdoc */
        setDifferedFromDefault: function () {
            var relatedField = uiRegistry.get(this.imports.relatedFieldPath);

            this._super();

            // as we have two fields stock_status(at product page and advanced inventory modal) we need to sync it.
            if (!_.isUndefined(relatedField) && parseFloat(relatedField.value()) !== parseFloat(this.value())) {
                relatedField.value(this.value());
            }

            if (parseFloat(this.initialValue) !== parseFloat(this.value())) {
                this.source.set(this.dataScope, this.value());
            } else if (!_.isUndefined(relatedField)) {
                this.source.remove(this.dataScope);
            }
        }
    });
});
