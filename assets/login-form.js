class loginForm {
    constructor ()
    {
        this._form = document.querySelector ('.login-form > form');
        if (!this._form) {
            alert ('Форма логина не найдена');
            return false;
        }
        this._login     = this._form.querySelector ('input[name=login]');
        this._password  = this._form.querySelector ('input[name=password]');
        this._btn       = this._form.querySelector ('input[type=submit]');
        this.setupEvents ();
        console.log (this._form);
    }

    setupEvents ()
    {
        this._form.onsubmit = (e) => {return this.onSubmit (e)};
        //this._btn.addEventListener ('click', e => {return this._form.submit (e)});
        this._login.focus ();
    }

    /**
     * 
     * @param {Event} e 
     */
    onSubmit (e)
    {
        if (!this.isValuesChecked ()) {
            alert ('!!!Не указаны логин или пароль');
            return false;
        }
        return true;
    }

    isValuesChecked ()
    {
        this._login.value = String(this._login.value).trim();
        this._password.value = String(this._password.value).trim();
        if (!this._login.value.length || !this._password.value.length) return false;
        return true;
    }
}

const myLogin = new loginForm ();