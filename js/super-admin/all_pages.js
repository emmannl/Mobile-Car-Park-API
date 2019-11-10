let html =
` <b>Notifications</b> 
  <b style="color: royalblue; margin-left: 65px;">Mark all as read</b>
    <b style="float: right; color: blue; padding-right: 10px;"><a
        href="./settings_account.html">Settings</a></b><br>
    <br>
<div class="notification-box">
    <div class="d-flex bd-highlight">
        <div class="px-">
            No notifications
        </div>
    </div>
</div>
<div style="background-color: royalblue; width: 100%">
    <b><a href="notification.html"
          style="color: white; padding-right: 10px; text-decoration: none;"> See All</a></b>
</div>
    `;

document.getElementById('myPopup').innerHTML = html;

let userName = document.querySelector('.main h1');
userName.innerHTML = `Welcome back ${userData.first_name}`;
document.querySelector('#navbarSupportedContent p > span.text-muted').innerHTML = `${userData.first_name} ${userData.last_name}`;