class Routes {
    constructor() {
        this._origin = "https://hng-car-park-api.herokuapp.com/api/v1";
    }

    origin() {
        return this._origin;
    }

    login() {
        console.log(this._origin);
        return `${this._origin}/auth/login`;
    }
}

const routes = new Routes;