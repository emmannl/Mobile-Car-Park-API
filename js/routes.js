class Routes {
    constructor() {
        this._origin = "https://hng-car-park-api.herokuapp.com/api/v1";
    }

    origin() {
        return this._origin;
    }

    loginAdmin() {
        return `${this._origin}/auth/login`;
    };

    loginUser() {
        return `${this._origin}/auth/login/user-email`;
    }

    user() {
        return `${this._origin}/user`;
    }

    allUsers() {
        return `${this._origin}/users`;
    }

    overviewStats() {
        return `${this._origin}/statistics/admin-overview`;
    }

    changePassword() {
        return `${this._origin}/user/password`;
    }
}

const routes = new Routes;