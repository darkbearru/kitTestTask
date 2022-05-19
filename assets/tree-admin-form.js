import {treeForm} from './tree-form.js';
import { makeQueryParams } from './my-fetch.js?1';

export class treeAdminForm extends treeForm {

    constructor (parent = false)
    {
        super ();
        this.parent = parent;
    }

    /**
     * Установка начальных переменных и обработка событий
     */
    setupForm ()
    {
        this.form = this.idForm.querySelector ('form');
        this.formHeader = this.idForm.querySelector ('form input[name=name]');
        this.formDescription = this.idForm.querySelector ('form textarea');
        this.btnSave = this.idForm.querySelector ('.btn-submit');
        this.btnCancel = this.idForm.querySelector ('.btn-cancel');

        this.formHeader.addEventListener ('keyup', (e) => this.changeStatus (e));
        this.formDescription.addEventListener ('keyup', (e) => this.changeStatus (e));
        this.btnCancel.addEventListener ('click', e => {return this.onCancel(e)});
        this.btnSave.addEventListener ('click', e => {return this.onSave(e)});
        this.form.addEventListener ('submit', e => {return this.onSave(e)});
        this._itemData  = false;
        this.changeStatus ();
    }

    showInfo (data)
    {
        this._itemData = data;
        this.formHeader.value = (data ? data.name : '');
        this.formDescription.value = (data ? data.text : '');
        this.changeStatus ();
    }

    changeStatus ()
    {
        let changed = false;
        let name = this.formHeader.value.trim ();
        let text = this.formDescription.value; 
        let isNew = false;
        if (this._itemData){
            isNew = this._itemData.isNew;
            if (
                isNew || 
                name !== this._itemData.name  || 
                text !== this._itemData.text  
            ) {
                changed = true;
            }
        }

        if ((name.length === text.length) && (name.length === 0)) {
            this.btnSave.setAttribute ("disabled", "");
            this.btnCancel.setAttribute ("disabled", "");
        } else if (changed) {
            this.btnSave.removeAttribute ("disabled");
            this.btnCancel.removeAttribute ("disabled");
        } else {
            this.btnSave.setAttribute ("disabled", "");
            this.btnCancel.setAttribute ("disabled", "");
        }
    }

    onCancel (e)
    {
        e.preventDefault();
        this.showInfo (this._itemData);
        return false;
    }

    onSave (e)
    {
        e.preventDefault ();
        let data = this._itemData;
        let name = this.formHeader.value.trim ();
        let text = this.formDescription.value; 
        data.name = name;
        data.text = text.replace(/\n/gi, "\\n");
       
        data.request = data.isNew ? 'POST' : 'PUT';
        fetch ('/api/' + makeQueryParams (data))
            .then (response => response.json ())
            .then (json => {
                if (json.result == 'ok') {
                    if (this.parent) {
                        data.id = (typeof json.id !== 'undefined' ? json.id : data.id);
                        data.upid = (typeof json.upid !== 'undefined' ? json.upid : data.upid);
                        this.parent.changeItemData (data);
                    }
                } else {
                    alert ("Error in console");
                    console.log (data.error);
                }
            });

        return false;
    }

    disableFormFields ()
    {
        this.formHeader.setAttribute ("disabled", "");
        this.formDescription.setAttribute ("disabled", "");
    }

    enableFormFields ()
    {
        this.formHeader.removeAttribute ("disabled");
        this.formDescription.removeAttribute ("disabled");
    }
}
