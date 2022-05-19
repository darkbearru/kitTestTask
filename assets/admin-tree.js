import { myTree } from './my-tree.js';
import { treeAdminForm } from './tree-admin-form.js';
import { myFetch, makeQueryParams } from './my-fetch.js?1';

export class adminTree extends myTree
{

    setupTree ()
    {
        super.setupTree();
        this._btnDelete     = document.querySelector (".tree-buttons__delete");
        this._btnAdd        = document.querySelector (".tree-buttons__add");
        this._btnAddChild   = document.querySelector (".tree-buttons__add-child");

        this.disableButtonsForSelection ();

        this._isEditing = false;
    }
    /**
     * Переопределяем функцию подключения формы, так как нам нужна админская
     */
    setupTreeForm ()
    {
        this._form = new treeAdminForm (this);
        this._form.disableFormFields ();
    }

    /**
     * Добавляем события на кнопки манипуляции с деревом
     */
    setupTreeEvents ()
    {
        super.setupTreeEvents ();
        this._btnDelete.addEventListener ('click', e => this.deleteItem ());
        this._btnAdd.addEventListener ('click', () => this.addNewItem ());
        this._btnAddChild.addEventListener ('click', () => this.addNewSubItem ());
    }

    /**
     * Если выбран элемент, необходимо показывать кнопку очистки выделения
     * @param {HTMLElement} item 
     */
    onItemSelected (item) 
    {
        if (this._isEditing) return false;
        super.onItemSelected (item);
        console.log (this._selected._data);
        this.enableButtonsForSelection ();
        this._form.enableFormFields ();
    }

    /**
     * После очистки выделения, прячем кнопку
     */
    clearSelection ()
    {
        if (this._isEditing) return false;
        super.clearSelection ();
        this.disableButtonsForSelection ();
        this._form.disableFormFields ();
    }

    /**
     * Добавление нового элемента
     */
    addNewItem ()
    {
        let li = this.createTreeItem ();
        li._data.isNew = true;
        if (this._selected) {
            this._selected.parentNode.insertBefore (li, this._selected);
        } else {
            this.idTree.appendChild (li);
        }
        this.onItemSelected (li.children[0]);
        this.disableAddButtons ();
        this._form.enableFormFields ();
    }

    /**
     * Создание потомка к выбранному элементу
     */
    addNewSubItem ()
    {
        if (!this._selected) return false;

        let li = this.createTreeItem ();
        li._data.isNew = true;
        li._data.upid = this._selected._data.id;
        let ul = this._selected.querySelector ('ul');
        if (!ul) {
            ul = document.createElement ('ul');
            ul.appendChild (li);
            this._selected.appendChild (ul);
        } else {
            ul.appendChild (li);
        }
        this.onItemSelected (li.children[0]);
        this.disableAddButtons ();
    }

    /**
     * Удаление выбранного элемента
     */
    deleteItem ()
    {
        if (!this._selected) return;

        let data = this._selected._data;
        
        if (!data.isNew) {
            data.request = "DELETE";
            fetch ('/api/' + makeQueryParams (data))
                .then (response => response.json ())
                .then (data => {
                    if (data.result == 'ok') {
                        this.deleteItemFromTree ();
                    }
                });
        } else {
            this.deleteItemFromTree ();
        }
        return true;
    }

    deleteItemFromTree ()
    {
        this._selected.parentNode.removeChild (this._selected);
        this._isEditing = false;
        this.clearSelection ();
        this.enableAddButtons ();
    }

    disableAddButtons ()
    {
        this._isEditing = true;
        this._btnAdd.setAttribute ("disabled", "");
        this._btnAddChild.setAttribute ("disabled", "");
    }

    enableAddButtons ()
    {
        this._isEditing = false;
        this._btnAdd.removeAttribute ("disabled");
        if (this._selected) {
            this._btnAddChild.removeAttribute ("disabled")
        }
    }

    disableButtonsForSelection ()
    {
        this._btnAddChild.setAttribute ("disabled", "");
        this._btnDelete.setAttribute ("disabled", "");
    }

    enableButtonsForSelection ()
    {
        if (!this._isEditing) {
            this._btnAddChild.removeAttribute ("disabled");
        }
        this._btnDelete.removeAttribute ("disabled");
    }

    changeItemData (data)
    {
        if (!this._selected) return false;

        this._selected.querySelector ('a').innerText = data.name;

        let {id,upid,name,text,childs} = data; 
        if (data.isNew && upid !== 0) {
            let _parent = this._selected.parentNode;
            if (_parent.nodeName === 'UL') {
                _parent = _parent.parentNode;
            }
            _parent._data.childs = true;
            _parent.classList.add ('has-childs');
        }

        this._selected._data = {id, upid, name, text, childs};
        this._isEditing = false;
        this.clearSelection ();

        if (data.isNew) {
            super.setupTreeEvents ();
            this.enableAddButtons ();
        }
    }

}
