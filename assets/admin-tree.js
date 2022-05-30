import { myTree } from './my-tree.js?8';
import { treeAdminForm } from './tree-admin-form.js?6';
import { myFetch, makeQueryParams } from './my-fetch.js';

export class adminTree extends myTree
{

    setupTree ()
    {
        this._btnDelete     = document.querySelector (".tree-buttons__delete");
        this._btnAdd        = document.querySelector (".tree-buttons__add");
        this._btnAddChild   = document.querySelector (".tree-buttons__add-child");

        this.disableButtonsForSelection ();

        this._isEditing = false;
        super.setupTree();

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
        this.makeSelectTree ();
    }

    /**
     * Если выбран элемент, необходимо показывать кнопку очистки выделения
     * @param {HTMLElement} item 
     */
    onItemSelected (item) 
    {
        if (this._isEditing) return false;
        super.onItemSelected (item);
        this.enableButtonsForSelection ();
        this._form.makeSelect (this.selectTree, this._selected);
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
            li._data.upid = this._selected._data.upid;
            this._selected.parentNode.insertBefore (li, this._selected);
        } else {
            this.idTree.appendChild (li);
        }
        this.onItemSelected (li.children[0]);
        this.disableAddButtons ();
        this._form.makeSelect (this.selectTree, this._selected);
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
            myFetch ('/api/', "DELETE", data)
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

    /**
     * Удаление элемента из дерева
     */
    deleteItemFromTree ()
    {
        this._selected.parentNode.removeChild (this._selected);
        this._isEditing = false;
        this.clearSelection ();
        this.enableAddButtons ();
    }

    /**
     * Отключаем все кнопки «добавления»
     */
    disableAddButtons ()
    {
        this._isEditing = true;
        this._btnAdd.setAttribute ("disabled", "");
        this._btnAddChild.setAttribute ("disabled", "");
    }

    /**
     * Включаем все кнопки добавления
     */
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

    /**
     * Изменяем данные в дереве, после сохранения в базу
     * @param {Object} data 
     * @returns {Boolean}
     */
    changeItemData (data)
    {
        if (!this._selected) return false;

        const oldUpid = this._selected._data.upid;


        this._selected.querySelector ('a').innerText = data.name;

        const {id, upid, name, description, childs} = data; 
        if (data.isNew && upid !== 0) {
            this.updateParent ();
        }

        this._selected._data = {id, upid, name, description, childs};

        if (upid != oldUpid) {
            this.changeTreeItemParent (upid);
        }

        this._isEditing = false;
        this.clearSelection ();

        if (data.isNew) {
            super.setupTreeEvents ();
            this.enableAddButtons ();
            this.makeSelectTree ();
        }
    }

    /**
     * Меняем класс и поле данных у родителя
     * в случае наличия потомков или их отсутствия
     * @param {Boolean} hasChilds 
     */
    updateParent (hasChilds = true)
    {
        if (this._selected._data.upid === 0) return false;

        let _parent = this._selected.parentNode;
        if (_parent.nodeName === 'UL') {
            _parent = _parent.parentNode;
        }
        if (_parent.nodeName === 'ASIDE') return false;

        _parent._data.childs = hasChilds;
        if (hasChilds){
            _parent.classList.add ('has-childs');
        } else {
            _parent.classList.remove ('has-childs');
        }

    }

    /**
     * Замена родителя у элемента
     * и все сопутствующие с этим действия
     * @param {Number} upid 
     */
    changeTreeItemParent (upid)
    {
        // Очищаем настройки старого родителя
        this.updateParent (false);
        
        const oldParent = this._selected.parentNode;

        // Ищем новый
        let newParent = this.findTreeItemById (upid);
        if (!newParent) {
            newParent = this.idTree;
            newParent.appendChild (this._selected);
        }else{
            let ul = newParent.querySelector('ul');
            if (!ul) {
                ul = document.createElement ('ul');
                newParent.appendChild (ul);
            }
            ul.appendChild (this._selected);
            this.updateParent (true);
        }
                

        // Подчищаем у старого родителя
        if (oldParent.children.length === 0) {
            oldParent.parentNode.removeChild (oldParent);
        }
        // Обновляем сохранённое дерево
        this.makeSelectTree ();
    }

    /**
     * Загрузка дерева из базы
     * переопределяем метод для вызова дополнительной обработки
     * @param {Array} data 
     */
    loadTreeFinished (data)
    {
        super.loadTreeFinished (data)
        //this.makeSelectTree ();
    }

    /**
     * Формирование из дерева списка родителя для select
     */
    async makeSelectTree ()
    {
        let res = [];
        this.selectTree = [];
        res = await this.getTreeItems (this.idTree, res);
        this.selectTree = res.result;
    }

    getTreeItems (parent, result, level = 0)
    {
        let offset = "&nbsp; &nbsp;".repeat (level + 1);
        let strict = [];
        const list = parent.children;

        for (let item of list) {
            let {id, name} = item._data;
            result.push ({id : Number(id), strict : [], name : offset + name, link: item});
            // Формируем список Id в которые нельзя помещать родителя
            strict.push (Number(id));
            let idx = result.length - 1;
            let ul = item.querySelector ('ul');
            //
            if (ul) {
                let res = this.getTreeItems (ul, result, level + 1);
                result = res.result;
                result[idx].strict = res.strict;
                strict = [...strict, ...res.strict];
            }
        }
        return {result, strict};
    }

    findTreeItemById (id)
    {
        id = Number (id);
        const item = this.selectTree.find (item => id == item.id);
        return item ? item.link : false;
    }    


}
