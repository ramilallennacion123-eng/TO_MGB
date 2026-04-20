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
