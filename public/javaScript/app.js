// get the buttuns to handle actions
const loginButtonForm = document.getElementById('login')
const registerButtonForm = document.getElementById('register')
const loginForm = document.getElementById('login-form')
const registerForm = document.getElementById('register-form')

// control switch between login form and register form
window.addEventListener('click', function (event) {
    //if login form button clicked
    if (event.target.id === 'login') {
        loginButtonForm.classList.add('clicked-button')
        registerButtonForm.classList.remove('clicked-button')
        loginForm.style.display = 'flex'
        registerForm.style.display = 'none'
    }
    //if register form button clicked
    if (event.target.id === 'register') {
        loginButtonForm.classList.remove('clicked-button')
        registerButtonForm.classList.add('clicked-button')
        loginForm.style.display = 'none'
        registerForm.style.display = 'flex'
    }
})

//basic client-side inputs validation. get main fields
const passwordInput = document.getElementById('password-input')
passwordInput.addEventListener('input', validatePassword)
const newPasswordInput = document.getElementById('register-password-1')
newPasswordInput.addEventListener('input', validateNewPassword)

//login password validation
function validatePassword() {
    const password = passwordInput.value;
    const errorMessage = document.getElementById('password-validation-message')
    if (password.length < 8) {
        errorMessage.innerText = 'password must be atleast 8 characters'
    } else {
        errorMessage.innerText = ''
    }
}
//register password validation
function validateNewPassword() {
    const password = newPasswordInput.value
    const errorMessage = document.getElementById('new-password-validation-message')
    if (password.length < 8) {
        errorMessage.innerText = 'password must be atleast 8 characters'
    } else {
        errorMessage.innerText = ''
    }
}

//get the message UI element to handle error messages
const errorBox = document.getElementById('error-box');
//login register error messages
function errorMessage(result) {
    errorBox.style.display = 'block';
    // render the message based on the result passed.
    document.getElementById('error-box-message').innerHTML = result;
}

//close error box
const closeBoxButton = document.getElementById('close-error-box')
closeBoxButton.addEventListener('click', closeErrorBox)
function closeErrorBox() {
    errorBox.style.display = 'none'
}

//ajax request from register endpoint to check the register result and show a message or next action based on it.
function handleRegisterSubmition() {
    const formData = new FormData(registerForm);
    fetch('http://localhost/VasHouseAssessment/public/index.php?url=registerAPI', {
        method: 'POST',
        // headers: {'Content-Type' : 'application/json'},
        body: formData
    }).then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    }).then(data => {
        var resultMessage = data.result;
        if (data.result !== 'user successfully registered') {
            errorMessage(resultMessage)
        } else {
            //if successfully registered then navigate to the main dashboard
            console.log("success registered")
            window.location.href = 'http://localhost/VasHouseAssessment/public/index.php?url=admindashboard&username';
        }

    }).catch(error => {
        console.error('error', error)
    })
}
// call/ handle register when form submited
registerForm.addEventListener('submit', function (event) {
    event.preventDefault();
    handleRegisterSubmition();
})
//ajax request from login endpoint to check the login result and show a message or next action based on it.
function handleLoginSubmit() {
    console.log('worked')
    const formData = new FormData(loginForm);
    fetch('http://localhost/VasHouseAssessment/public/index.php?url=loginAPI', {
        method: 'POST',
        body: formData
    }).then(response => {
        if (!response.ok) {
            throw new Error('network response was not ok')
        }
        return response.json();
    }).then(data => {
        //access the message and name properties from data object
        const { message, name } = data.result
        if (message !== 'success user login') {
            errorMessage(message)
        } else {
                //if successfully registered then navigate to the main dashboard
                window.location.href = 'http://localhost/VasHouseAssessment/public/index.php?url=admindashboard&username=' + encodeURIComponent(name);
        }
    }).catch(error => {
        console.error(error)
    })
}
// call/ handle sign in when form submited
loginForm.addEventListener('submit', function (event) {
    event.preventDefault();
    handleLoginSubmit();
})

