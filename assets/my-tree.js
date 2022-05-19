import {treeForm} from './tree-form.js';

export class myTree
{
    /**
     * Конструктор, в качестве параметра получает наименование Id корневого UL дерева
     * Если не находит, то останавливает работу
     * @param {string} idTree 
     * @returns 
     */
    constructor (idTree = "my-tree") 
    {
        this.idTree = document.getElementById (idTree);
        if (!this.idTree) {
            alert("Tree not found");
            return false;
        }
        this.setupTree ();
    }

    /**
     * Установка начальных переменных, а так же обработчиков событий
     * А также загрузка дерева в случае если оно пустое
     */
    setupTree ()
    {
        this._selected = false;

        if (this.idTree.children.length !== 0) {
            this.loadTreeFromHtml (this.idTree.children);
            this.setupTreeEvents ();
        } else {
            this.loadTree ();
        }
        this.setupDocumentClick ();
        this.setupTreeForm ();
    }

    setupTreeForm ()
    {
        this._form = new treeForm ();
    }

    /**
     * Загружаем начальное дерево в случае если оно уже указано в html
     * @param {HTMLCollection} list 
     */
    loadTreeFromHtml (list)
    {
        for (let item of list) {
            if (!item._data) {
                let itemData = this.getTreeItemData (item);
                item._data = {...itemData};
            }
            let childs = item.querySelector ('ul');
            if (childs && childs.children.length) {
                item._data.childs = this.loadTreeFromHtml (childs.children);
                item.classList.add ('has-childs');
            } 
        }
        return true;
    }

    /**
     * Получаем данные у атрибутов data-id, data-upid, data-name, data-text у вложенного span
     * @param {HTMLElement} item 
     */
    getTreeItemData (item)
    {
        let span = item.querySelector ('span');
        if (!span) return {};
        return {
            id : span.getAttribute ('data-id'),
            upid : span.getAttribute ('data-upid'),
            name : span.getAttribute ('data-name'),
            text: span.getAttribute ('data-text'),
            childs : []
        }
    }

    /**
     * Устанавливаем обработчики событий
     */
    setupTreeEvents ()
    {
        let li = this.idTree.querySelectorAll ("li:not(.ready)");
        li.forEach (item => {
            this.setupLiEvents (item);
            item.classList.add("ready");
        });
    }

    setupDocumentClick ()
    {
        document.addEventListener ('click', e => {
            let strictNodes = ['A', 'BUTTON', 'INPUT', 'TEXTAREA'];
            if (strictNodes.indexOf (e.target.nodeName) === -1) {
                this.clearSelection ();
            }
        });
    }

    /**
     * Устанавливаем события на элемент дерева:
     * - Сварачивание / разварачивание
     * - Выделение
     * @param {HTMLElement} item 
     */
    setupLiEvents (item)
    {
        let span = item.querySelector ('span');
        span.addEventListener ('click', e => {
            this.onTreeItemClick (e.target);
            e.stopPropagation ();
            e.preventDefault ();
            e.cancelBubble = true;
        });        
    }

    /**
     * Клик на span элемента дерева
     * Либо выделяем элемент дерева, либо разварачиваем его
     * @param {HTMLElement} item 
     */
    onTreeItemClick (item)
    {
        if (item.nodeName === 'A') {
            item = item.parentNode;
        }
        if (item.nodeName === 'SPAN') {
            this.onItemSelected (item);
        } else {
            let li = item.parentNode.parentNode;
            if (li.classList.contains ('has-childs')) {
                li.classList.toggle ('is-open');
            }
        }
    }

    /**
     * Выделение элемента стилями
     * а также отображение данных в форме
     * @param {HTMLElement} item 
     */
    onItemSelected (item)
    {
        let li = item.parentNode;
        li.classList.toggle ('selected');
        if (li.classList.contains ('selected')) {
            if (this._selected) {
                this._selected.classList.remove ('selected');
            }
            this._selected = li;
            if (this._form && li._data) this._form.showInfo (li._data);
        } else {
            if (this._form) this._form.showInfo (false);
        }
    }

    /**
     * Очищаем выделение
     */
    clearSelection ()
    {
        if (this._selected) {
            this._selected.classList.remove ('selected');
        }
        this._selected = false;
        if (this._form) this._form.showInfo ('', '');
    }

    /**
     * Загрузка данных формы из скрипта
     */
    loadTree ()
    {
        fetch ('/api/')
            .then (response => response.json ())
            .then (data => this.loadTreeFinished (data));
    }

    /**
     * Окончание загрузки данных
     * @param {Object} data 
     */
    loadTreeFinished (data)
    {
        let buffer = this.createHTMLTree (data);
        this.idTree.innerHTML = '';
        this.idTree.appendChild(buffer);
        this.setupTreeEvents ();
    }

    /**
     * Проходим по переданным данным и создаём 
     * на основе этого дерево в буффере 
     * @param {Object} data 
     */
    createHTMLTree (data) 
    {
        const frag = document.createDocumentFragment();
	    for (let i in data) {
			if (!data.hasOwnProperty (i)) continue;
            let item = data[i];
            frag.appendChild (this.createTreeItem (data[i]));
            // frag.appendChild (li);
        }
        return frag;
    }

    createTreeItem (item)
    {
        item = item || {
            id: "",
            upid: "",
            name: "<<< Новый элемент >>>",
            text: "<<< Описание нового элемента >>>"
        };
        let li = document.createElement ('li');
        li._data = {id: item.id, upid: item.upid, name: item.name, text: item.text, childs: (item.childs ? true : false)};
        li.innerHTML = `<span data-id="${item.id}"><i></i><a href="">${item.name}</a></span>`;
        if (item.childs) {
            let ul = document.createElement ('ul');
            ul.appendChild (this.createHTMLTree (item.childs));
            li.appendChild (ul);
            li.classList.add ('has-childs');
        }
        return li;
    }
}
