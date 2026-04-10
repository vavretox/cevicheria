window.orderEditorUtils = window.orderEditorUtils || (() => {
    function normalizeServiceType(serviceType) {
        return serviceType === 'takeaway' ? 'takeaway' : 'dine_in';
    }

    function getServiceTypeLabel(serviceType) {
        return normalizeServiceType(serviceType) === 'takeaway' ? 'Para llevar' : 'En mesa';
    }

    function normalizeNotes(notes = '') {
        return String(notes || '').trim();
    }

    function buildItemSignature(productId, notes = '', serviceType = 'dine_in', unitPrice = null, includeUnitPrice = false) {
        let signature = `${Number(productId) || 0}::${normalizeNotes(notes).toLowerCase()}::${normalizeServiceType(serviceType)}`;

        if (includeUnitPrice) {
            signature += `::${Number(unitPrice || 0).toFixed(2)}`;
        }

        return signature;
    }

    function mergeOrderItem(items, newItem, options = {}) {
        const includeUnitPriceInSignature = Boolean(options.includeUnitPriceInSignature);
        const normalizedItem = {
            ...newItem,
            service_type: normalizeServiceType(newItem.service_type),
            service_type_label: newItem.service_type_label || getServiceTypeLabel(newItem.service_type),
            notes: normalizeNotes(newItem.notes),
            quantity: parseInt(newItem.quantity, 10) || 0,
            unit_price: Number(newItem.unit_price || 0),
        };

        const newSignature = buildItemSignature(
            normalizedItem.product_id,
            normalizedItem.notes,
            normalizedItem.service_type,
            normalizedItem.unit_price,
            includeUnitPriceInSignature
        );

        const existing = items.find((item) => {
            return buildItemSignature(
                item.product_id,
                item.notes,
                item.service_type,
                item.unit_price,
                includeUnitPriceInSignature
            ) === newSignature;
        });

        if (existing) {
            existing.quantity = (parseInt(existing.quantity, 10) || 0) + normalizedItem.quantity;
            existing.service_type = normalizedItem.service_type;
            existing.service_type_label = normalizedItem.service_type_label;

            if (normalizedItem.image_url && !existing.image_url) {
                existing.image_url = normalizedItem.image_url;
            }

            return existing;
        }

        if (!normalizedItem.item_key) {
            normalizedItem.item_key = typeof options.createItemKey === 'function'
                ? options.createItemKey(normalizedItem)
                : newSignature;
        }

        items.push(normalizedItem);
        return normalizedItem;
    }

    function isFoodProduct(product) {
        if (!product) {
            return false;
        }

        const categoryCode = String(product.category?.code || '').trim().toLowerCase();
        return categoryCode !== 'bebidas';
    }

    function getCheckedValues(selector) {
        return $(`${selector}:checked`).map(function() {
            return this.value;
        }).get().join(', ');
    }

    function syncToggleCardState(containerSelector, inputSelector, activeClasses = ['border-success', 'bg-success-subtle']) {
        $(containerSelector).each(function() {
            const input = $(this).find(inputSelector);
            $(this).toggleClass(activeClasses.join(' '), input.is(':checked'));
        });
    }

    return {
        buildItemSignature,
        getCheckedValues,
        getServiceTypeLabel,
        isFoodProduct,
        mergeOrderItem,
        normalizeServiceType,
        syncToggleCardState,
    };
})();
