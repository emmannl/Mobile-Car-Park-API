const getUsersSlots = () => {
    const url = "https://hng-car-park-api.herokuapp.com/api/v1/users";
    let name = document.getElementById('name');
    let em = document.getElementById('em');
    let role = document.getElementById('role');
    let ll = document.getElementById('ll');
    makeGetRequest(url).then(data => {
        const userData = data.data;
        console.log(userData)
        for(let i = 0; i < userData.length; i++) {
            name.innerHTML += '<p>' + userData[i].first_name + ' ' + userData[i].last_name + '</p>' + '<br>';
            em.innerHTML += '<p>' + userData[i].email + '</p>' + '<br>';
            role.innerHTML += '<p>' + userData[i].role + '</p>' + '<br>';
            ll.innerHTML += '<p>' + userData[i].last_login + '</p>' + '<br>';
        }

    })
};


const makeGetRequest = (url) => {

    return fetch(url, {
        method: "GET",
        headers: authHeaders(),
    }).then(response => {
        if (response.ok){
            Swal.fire("List of Users updated);
            return response.json()
        } else {
            Swal.fire('User List Update failed');
        }
    })
};


const authHeaders = () => {

    let token = localStorage.getItem('token');
    alert(token)

    return {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
    }
}