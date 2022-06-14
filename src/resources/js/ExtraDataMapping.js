var ChannelEngine = window.ChannelEngine || {};

document.addEventListener(
    'DOMContentLoaded',
    function () {
        const addNewAttribute = document.getElementById('ceAddNewAttribute');
        let hasEmpty = false;
        addNewAttribute.onclick = () => {
            const extraDataMapping = document.querySelectorAll('.ce-input-extra-data');
            let keys = [];
            extraDataMapping.forEach(mapping => {
                if (mapping.id === 'hidden') {
                    return;
                }
                let elements = mapping.firstElementChild.children;
                let key = elements.item(1).value;
                hasEmpty = key.match(/^ *$/) !== null
                keys.push(key);
            });
            let isValid = !hasEmpty && !keys.some(x => keys.indexOf(x) !== keys.lastIndexOf(x)) //without duplicates
            if (isValid) {
                ChannelEngine.productService.makeExtraDataForm('');
            }
        }
    }
);