let accesstoken = loa=localStorage.getItem('token');
let userInfo = localStorage.getItem('user');
if (!accesstoken) {
    location.replace('/admin/index.html');
}

// to verify the user profile
// attempt to get user details
let request = new Request(routes.user(), {
    headers: new Headers({
        'Accept': 'application/json',
        'Authorization': accesstoken,
    }),
});
fetch(request)
    .then(response => response.json())
    .then(response => {
        userInfo =  response.data;
        //update details, incase of changes
        localStorage.setItem('user', JSON.stringify(userInfo));
    })
.catch(error => {
    // redirect to login
    return window.location.replace(`/admin/index.html`);
});

const bearerToken = accesstoken;
const userData = JSON.parse(userInfo);
