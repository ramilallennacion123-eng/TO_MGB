function addPurpose() {
    const container = document.getElementById('purpose-container');
    const btnGroup = container.querySelector('.button-group');

    const row = document.createElement('div');
    row.style.display = "flex";
    row.style.gap = "10px";
    row.style.marginTop = "10px";
    row.style.alignItems = "center";

    const newInput = document.createElement('input');
    newInput.type = 'text';
    newInput.name = 'Purpose[]'; 
    newInput.style.flex = "1";

    const removeBtn = document.createElement('button');
    removeBtn.innerHTML = "✕";
    removeBtn.type = "button";
    removeBtn.style.backgroundColor = "#e74c3c";
    removeBtn.style.color = "white";
    removeBtn.onclick = function() {
        row.remove();
    };
    row.appendChild(newInput);
    row.appendChild(removeBtn);
    container.insertBefore(row, btnGroup);
} 


function addAssistant() {
    const container = document.getElementById('assistant-container');
    const btnGroup = container.querySelector('.button-group');

    const row = document.createElement('div');
    row.style.display = "flex";
    row.style.gap = "10px";
    row.style.marginTop = "10px";
    row.style.alignItems = "center";

    const newInput = document.createElement('input');
    newInput.type = 'text';
    newInput.name ='Assistants[]';
    newInput.placeholder = '';
    newInput.style.flex = "1";

    const removeBtn = document.createElement('button');
    removeBtn.innerHTML = "✕";
    removeBtn.type = "button";
    removeBtn.style.backgroundColor = "#e74c3c";
    removeBtn.style.padding = "10px 15px";
    removeBtn.onclick = function() {
        row.remove();
    };

    const submitBtn = document.createElement('button');
    submitBtn.innerHTML = "Submit";
    submitBtn.type = "button";
    submitBtn.style.backgroundColor = "#2ecc71";
    submitBtn.style.padding = "10px 15px";

    row.appendChild(newInput);
    row.appendChild(removeBtn);
    container.insertBefore(row, btnGroup);
}

    document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form');

    loadFormData();

    form.addEventListener('input', saveFormData);

    function saveFormData() {
        const formData = new FormData(form);
        const formObject = {};

        formData.forEach((value, key) => {
            if (!Reflect.has(formObject, key)) {
                formObject[key] = value;
            } else {
                if (!Array.isArray(formObject[key])) {
                    formObject[key] = [formObject[key]];
                }
                formObject[key].push(value);
            }
        });

        sessionStorage.setItem('travelOrderData', JSON.stringify(formObject));
    }

    function loadFormData() {
        const savedData = sessionStorage.getItem('travelOrderData');
        
        if (savedData) {
            const formObject = JSON.parse(savedData);

            Object.keys(formObject).forEach(key => {
                const value = formObject[key];
                if (Array.isArray(value)) {
                    let inputs = document.querySelectorAll(`[name="${key}"]`);
                    while (inputs.length < value.length) {
                        if (key === 'Purpose[]' && typeof window.addPurpose === 'function') {
                            window.addPurpose();
                        } else if (key === 'Assistants[]' && typeof window.addAssistant === 'function') {
                            window.addAssistant();
                        } else {
                            break; 
                        }

                        inputs = document.querySelectorAll(`[name="${key}"]`);
                    }

                    value.forEach((val, index) => {
                        if (inputs[index]) {
                            inputs[index].value = val;
                        } 
                    });
                } else {
                    const input = document.querySelector(`[name="${key}"]`);
                    if (input) {
                        input.value = value;
                    }
                }
            });
        }
    }
});
function clearSession() {
    sessionStorage.removeItem('travelOrderData');
}
