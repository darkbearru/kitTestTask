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

        this.formHeader = this.form.querySelector ('input[name=name]');
        this.formText = this.form.querySelector ('textarea');
        this.formParent = this.form.querySelector ('select');
        this.btnSave = this.form.querySelector ('.btn-submit');
        this.btnCancel = this.form.querySelector ('.btn-cancel');

        this.formHeader.addEventListener ('keyup', (e) => this.changeStatus (e));
        this.formText.addEventListener ('keyup', (e) => this.changeStatus (e));
        this.formParent.addEventListener ('change', (e) => this.changeStatus (e));
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
        this.formText.value = (data ? data.text : '');
        if (!data) {
            this.formParent.innerHTML = '';
        }
        this.changeStatus ();
    }

    changeStatus ()
    {
        let changed = false;
        let name = this.formHeader.value.trim ();
        let text = this.formText.value; 
        let upid = this.formParent.value;
        let isNew = false;

        if (this._itemData){
            isNew = this._itemData.isNew;
            if (
                isNew || 
                name !== this._itemData.name  || 
                text !== this._itemData.text ||
                upid != this._itemData.upid
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
        let text = this.formText.value;
        let upid = this.formParent.value;
                
        data.upid = upid;
        data.name = name;
        data.text = text.replace(/\n/gi, "\\n");
       
        data.request = data.isNew ? 'POST' : 'PUT';
        fetch ('/api/' + makeQueryParams (data))
            .then (response => response.json ())
            .then (json => {
                if (json.result == 'ok') {
                    if (this.parent) {
                        data.text = text;
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
        this.formText.setAttribute ("disabled", "");
        this.formParent.setAttribute ("disabled", "");
    }

    enableFormFields ()
    {
        this.formHeader.removeAttribute ("disabled");
        this.formText.removeAttribute ("disabled");
        this.formParent.removeAttribute ("disabled");
    }

    /**
     * Формируем Select на основе переданных данных
     */
    makeSelect (list, item)
    {
        const selected = (item ? Number(item._data.upid) : 0);
        const id = (item ? Number(item._data.id) : false);

        let options = '<option value="0">Корень</option>';
        let strict = list.find (el => el.id === id);
        strict = strict ? strict.strict : [];

        for (let option of list) {
            if (id !== option.id && strict.indexOf(option.id) === -1) {
                options += `<option value="${option.id}"` + 
                            (option.id === selected ? " selected" : "") + 
                            `>${option.name}</option>`;
            }
        }
        this.formParent.innerHTML = options;
        this.changeStatus ();
    }
}
