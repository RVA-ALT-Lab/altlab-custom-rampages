document.addEventListener('DOMContentLoaded', function(){
  var lostPasswordLink = document.querySelectorAll("#login #nav a")[1];

  if (lostPasswordLink.innerText.includes('password')) {
    lostPasswordLink.href = "/help/password-help/"
  }

  var loginBox = document.querySelector('#login');
  var loginForm = document.querySelector('#login #loginform');
  if (loginForm === null) {
    loginForm = document.querySelector('#login #lostpasswordform')
    if (loginForm !== null) {
      loginForm.style.display = "none";
    }
  }

  var message = document.createElement('p');
  message.classList.add('message');
  message.innerHTML = 'We are currently having an issue with most users not receiving password reset emails for @vcu.edu, @mymail.vcu.edu, and @vcuhealth.org email addresses. Please request <a href="/help/password-help/">a manual password reset using this form.</a>';
  loginBox.insertBefore(message, loginForm);

})
