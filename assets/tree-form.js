export class treeForm {
    /**
     * Находим форму в кторой потом отображаем нужную нам инфу
     * @param {string} idForm 
     */
    constructor (idForm = 'tree-form')
    {
        this.idForm = document.getElementById (idForm);
        if (!this.idForm) {
            alert("Form not found");
            return false;
        }
        this.setupForm ();
    }

    /**
     * Установка начальных переменных и обработка событий
     */
    setupForm ()
    {
        this.formHeader = this.idForm.querySelector ('h3');
        this.formDescription = this.idForm.querySelector ('p');
        this._itemData  = false;
    }

    showInfo (data = false)
    {
        this._itemData = data;
        this.formHeader.innerText = (data ? data.name : '');
        this.formDescription.innerHTML = (data ? data.description.replace(/\n/gi, "<br />\n") : '');
    }
}
