export class treeForm {
    /**
     * Находим форму в кторой потом отображаем нужную нам инфу
     * @param {string} idForm 
     */
    constructor (idForm = 'tree-form')
    {
        this.idForm = document.getElementById (idForm);
    }
}