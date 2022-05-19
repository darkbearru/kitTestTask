import {treeForm} from './tree-form.js';

export class treeAdminForm extends treeForm {

    /**
     * Установка начальных переменных и обработка событий
     */
    setupForm ()
    {
        this.formHeader = this.idForm.querySelector ('form input[name=name]');
        this.formDescription = this.idForm.querySelector ('form textarea');
    }

    showInfo (name = '', description = '')
    {
        this.formHeader.value = name;
        this.formDescription.value = description;
    }
}
