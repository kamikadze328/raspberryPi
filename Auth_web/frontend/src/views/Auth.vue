<!--suppress HtmlFormInputWithoutLabel -->
<template>
  <div class="form-wrapper" @click="click">
    <form ref="form" class="login-form" v-on:submit.prevent="checkForm">
      <input :id="'first-' + authCode" v-model="action" checked="" class="first-input" name="action" type="radio"
             value="1">
      <label :for="'first-' + authCode"
             :style="{'cursor': authCode === this.$mydata.LOCAL_AUTH_CODES.CHANGING_PASSWORD ? 'default' : 'pointer'}"
             class="disable-selection-text">
        {{ viewText.first }}
      </label>
      <input :id="'second-' + authCode" v-model="action" class="second-input" name="action" type="radio" value="2">
      <label :for="'second-' + authCode"
             :style="{'cursor': authCode === this.$mydata.LOCAL_AUTH_CODES.CHANGING_PASSWORD ? 'default' : 'pointer'}"
             class="disable-selection-text">
        {{ viewText.second }}
      </label>
      <div class="form-inside-wrapper">
        <div class="arrow"></div>
        <div :class="{'invalid':!firstInputValid}" class="warning">
          <input :id="'name-' + authCode" v-model="firstInput" :placeholder="inputText.first" maxlength="25"
                 minlength="3"
                 type="text" class="pretty-input">
          <span :data-validate="errors.name"></span>
        </div>
        <div :class="{'invalid':!passwordValid}" class="warning">
          <input :id="'password-' + authCode" v-model="password" :placeholder="inputText.second" maxlength="25"
                 minlength="3"
                 type="password" class="pretty-input">
          <span :data-validate="errors.password"></span>
        </div>
        <div :class="{'invalid':!confirmValid}" class="warning">
          <input :id="'confirm-' + authCode" v-model="passwordConfirm" :placeholder="inputText.third" maxlength="25"
                 minlength="3"
                 type="password" class="pretty-input">
          <span :data-validate="errors.passwordConfirm"></span>
        </div>
      </div>
      <button type="submit">
        <span class="disable-selection-text">
          <br>
          {{ submitButtonText.first }}
          <br>
          {{ submitButtonText.second }}
        </span>
      </button>
      <div class="response-message">
        <p ref="message" :class="response.status ? 'green' : 'red'" class="visible">
          {{ response.message }}</p>
      </div>
    </form>
  </div>
</template>

<script>
export default {
  name: "Auth",
  props: {
    authCode: Number
  },
  data() {
    return {
      ACTIONS: {
        REGISTER: 'register',
        LOGIN: 'login',
        CHANGING_PASSWORD: 'change_password'
      },
      action: '1',
      errors: {
        name: '',
        password: '',
        passwordConfirm: ''
      },
      response: {
        status: null,
        message: ''
      },
      success: '',
      firstInput: null,
      password: null,
      passwordConfirm: null,
      firstInputValid: true,
      passwordValid: true,
      confirmValid: true
    }
  },
  computed: {
    isAuth() {
      return this.authCode === this.$mydata.LOCAL_AUTH_CODES.LOGIN
    },
    isChangingPassword() {
      return this.authCode === this.$mydata.LOCAL_AUTH_CODES.CHANGING_PASSWORD
    },
    isRegistration() {
      return this.authCode === this.$mydata.LOCAL_AUTH_CODES.REGISTER
    },
    viewText() {
      let first, second
      if (this.isAuth) {
        first = 'Вход'
        second = ''
      } else if (this.isChangingPassword) {
        first = ''
        second = 'Изменение пароля'
      }
      return {
        first,
        second
      }
    },
    inputText() {
      let first, second, third
      if (this.isAuth) {
        first = 'Имя'
        second = 'Пароль'
        third = ''
      } else if (this.isChangingPassword) {
        first = 'Старый пароль'
        second = 'Новый пароль'
        third = 'Повторите новый пароль'
      }
      return {
        first,
        second,
        third
      }
    },
    submitButtonText() {
      let first, second
      if (this.isAuth) {
        first = 'Войти'
        second = 'Создать аккаунт'
      } else if (this.isChangingPassword) {
        first = ''
        second = 'Изменить'
      }
      return {
        first,
        second
      }
    },
    actionName() {
      let action = ''
      if (this.isAuth)
        action = this.ACTIONS.LOGIN
      else if (this.isChangingPassword)
        action = this.ACTIONS.CHANGING_PASSWORD
      else if (this.isRegistration)
        action = this.ACTIONS.REGISTER
      return action
    }
  },
  watch: {
    actionName() {
      this.clearForm()
    },
  },
  methods: {
    click(e) {
      if (!(this.$refs['form'] === e.target || this.$refs['form'].contains(e.target))){
        this.clearForm()
        this.$emit('close')
      }
    },
    setMessage(message, isOK) {
      this.response.status = isOK
      this.response.message = message
      const elem = this.$refs['message']
      elem.classList.remove('visible')
      setTimeout(() => elem.classList.add('visible'), 150)
    },
    registerRequest() {
      this.request(this.actionName).catch(result => {
        console.log(result)
        if (result) {
          this.setMessage('Аккаунт успешно создан!', true)
        }
      })
    },
    loginRequest() {
      this.request(this.actionName).then(result => {
        console.log(result)

        if (result) {
          this.$emit('successful-login', this.firstInput)
          this.$router.push({name: 'profile'})
        }

      })
    },
    changePasswordRequest() {
      this.request(this.actionName).then(result => {
        console.log(result)
        if (result) {
          console.log('Успешно!')
        }

      })
    },
    request(actionName) {
      let url = ''
      let data = {}
      if (this.isAuth) {
        url = this.$mydata.URL.auth
        data.login = this.firstInput
        data.password = this.password
      } else if (this.isChangingPassword) {
        url = this.$mydata.URL.changePassword
        data.login = this.$mydata.currentName()
        data.oldPassword = this.firstInput
        data.newPassword = this.password
        console.log(data)

      }
      data.purpose = actionName
      return this.$axios({
        timeout: 5000,
        method: 'post',
        url,
        data
      }).then((response) => {
        console.log(response)
        if (response.data) {
          if (response.data.error) {
            throw response.data.error
          } else if (response.data.code !== 200) {
            this.setMessage('Что-то пошло не так', false)
            return false
          } else {
            this.setMessage(response.data.message, true)
            return true
          }
        } else throw response

      }).catch(error => {
        console.log(error)
        if (error.code) {
          this.response.status = error.code;
          this.setMessage(error.message, false)

        } else {
          //js error
          if (error.message === 'NetworkError') {
            this.setMessage('Нет соединения с интернетом', false)
          } else {
            this.setMessage('Ошибка сервера', false)
          }
        }
        return false
      })
    },
    checkForm() {
      if (!this.firstInput) {
        this.firstInputValid = false;
        if (this.isAuth)
          this.errors.name = 'Укажите имя'
        else if (this.isChangingPassword)
          this.errors.name = 'Укажите старый пароль'
      } else if (!this.firstInput.match(/^[A-Za-z0-9]*$/)) {
        this.firstInputValid = false;
        this.errors.name = 'Только латинские буквы и цифры';
      } else {
        this.firstInputValid = true;
      }

      if (!this.password) {
        this.passwordValid = false;
        if (this.isAuth)
          this.errors.password = 'Укажите пароль'
        else if (this.isChangingPassword)
          this.errors.password = 'Укажите новый пароль'
      } else if (!this.password.match(/^[A-Za-z0-9]*$/)) {
        this.passwordValid = false;
        this.errors.password = 'Только латинские буквы и цифры';
      } else {
        this.passwordValid = true;
      }

      if (this.actionName === this.ACTIONS.LOGIN) {
        this.confirmValid = true
      } else if (!this.passwordConfirm) {
        this.confirmValid = false
        this.errors.passwordConfirm = 'Укажите пароль ещё раз'
      } else if (this.password !== this.passwordConfirm) {
        this.confirmValid = false
        this.errors.passwordConfirm = 'Пароли не совпадают'
      } else {
        this.confirmValid = true
      }

      console.log(this.action)
      console.log(this.firstInputValid && this.passwordValid && this.confirmValid)
      if (this.firstInputValid && this.passwordValid && this.confirmValid) {
        if (this.actionName === this.ACTIONS.REGISTER)
          this.registerRequest()
        else if (this.actionName === this.ACTIONS.LOGIN)
          this.loginRequest()
        else if (this.actionName === this.ACTIONS.CHANGING_PASSWORD)
          this.changePasswordRequest()
      }
    },
    clearForm() {
      console.log('clear')
      this.firstInputValid = true;
      this.passwordValid = true;
      this.confirmValid = true;
      this.response.message = ''
      this.passwordConfirm = ''
      this.firstInput = ''
      this.password = ''
    }

  },
  mounted() {
    this.clearForm()
    if (this.isAuth)
      this.action = '1'
    else if (this.isChangingPassword)
      this.action = '2'
  },


}
</script>

<style scoped>
.form-inside-wrapper, label, .arrow, button span {
  transition: height, transform .5s, .5s ease, -webkit-transform .5s;
}

.form-inside-wrapper {
  overflow: hidden;
}

.first-input:checked ~ .form-inside-wrapper {
  height: 126px;
}

.first-input:checked ~ .form-inside-wrapper .arrow {
  left: 10px;
}

.first-input:checked ~ button span {
  transform: translate3d(0, -48px, 0);
}

.second-input:checked ~ .form-inside-wrapper {
  height: 184px;
}

.second-input:checked ~ .form-inside-wrapper .arrow {
  left: 108px;
}

.second-input:checked ~ button span {
  transform: translate3d(0, -96px, 0);
}

form {
  max-width: 450px;
  width: 100%;
  min-width: 180px;
  margin: 0 15px;
}

input[type=radio] {
  display: none;
}

label {
  cursor: pointer;
  display: inline-block;
  font-size: 16px;
  font-weight: 800;
  opacity: .5;
  margin-bottom: 15px;
  text-transform: uppercase;
}

label:hover {
  transition: all .3s cubic-bezier(.6, 0, .4, 1);
  opacity: 1;
}

label {
  margin-right: 20px;
}

input[type=radio]:checked + label {
  opacity: 1;
}

button {
  border: 2px solid #348fe2;
  color: #348fe2;
  border-radius: 8px;
  cursor: pointer;
  font-size: 18px;
  height: 48px;
  width: 100%;
  margin-bottom: 10px;
  overflow: hidden;
  transition: all .3s cubic-bezier(.6, 0, .4, 1);
}

button span {
  display: block;
  line-height: 48px;
  position: relative;
  top: 0;
  transform: translate3d(0, 0, 0);
}

button:hover {
  background: #348fe2;
  color: white;
}

.arrow {
  height: 0;
  width: 0;
  border-bottom: 10px solid #fff;
  border-left: 10px solid transparent;
  border-right: 10px solid transparent;
  position: relative;
  left: 32px;
}

.login-form {
  position: relative;
  background-color: #f7f7f7;
  padding: 30px;
  border-radius: 4px;
}

.warning {
  position: relative;
  display: flex;
  align-items: center;
  margin-bottom: 10px;
}

.warning > span {
  visibility: hidden;
  position: absolute;
  right: 16px;
  width: 1rem;
  height: 1rem;
  background: url("../assets/warning.svg") no-repeat;
  background-size: 1rem;
  transition: opacity 0.5s ease;
  transform-style: preserve-3d;
}

.warning > span::after {
  content: attr(data-validate);
  position: absolute;
  visibility: hidden;
  opacity: 0;
  right: -5px;
  top: 50%;
  padding: 0 28px 0 4px;
  font-size: 0.9rem;
  font-weight: 500;
  color: #FF5B57;
  white-space: nowrap;
  transform: translateY(-50%) translateZ(-1px);
  transition: opacity .2s;
}

.invalid span {
  visibility: visible !important;
  opacity: 1;
}

.invalid input {
  border-color: #FF5B57;
}

.invalid:hover span::after {
  visibility: visible;
  opacity: 1;
}

.response-message {
}

.response-message p {
  opacity: 0;
  visibility: hidden;
  transition: opacity .15s;
}

.visible {
  opacity: 1 !important;
  visibility: visible !important;
}
</style>