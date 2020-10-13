<!--suppress HtmlFormInputWithoutLabel -->
<template>
  <div class="form-wrapper" @click="click">
    <form ref="form" class="login-form" v-on:submit.prevent="checkForm">
      <span :class="numberOfInputs.classText" class="disable-selection-text auth-name">{{ viewText }}</span>
      <div class="form-inside-wrapper">
        <div class="arrow"></div>
        <div :class="{'invalid':!firstInputValid}" class="warning" v-show="numberOfInputs.number >= 1">
          <input v-model="firstInput" :disabled="disabledFirstInput" :placeholder="inputText.first"
                 class="pretty-input"
                 maxlength="50" minlength="3"
                 type="text">
          <span :data-validate="errors.firstInput"></span>
        </div>
        <div :class="{'invalid':!selectInputValid}" class="warning select-box" v-show="isUserCreating">
        <v-select :options="$mydata.data.roles" label="name" v-model="selectInput"
                  :clearable="false" :placeholder="inputText.select"
                  :disabled="disabledSelectInput"></v-select>
          <span :data-validate="errors.firstInput"></span>
        </div>
        <div :class="{'invalid':!secondInputValid}" class="warning" v-show="(numberOfInputs.number >= 3 && isUserCreating) || (numberOfInputs.number >= 2 && !isUserCreating)">
          <input v-model="secondInput" :disabled="disabledSecondInput" :placeholder="inputText.second"
                 class="pretty-input"
                 :class="{'answer-input':hasAlreadyReset}"
                 maxlength="200" minlength="3"
                 :type="typeSecondInput">
          <span :data-validate="errors.firstInput"></span>
        </div>

        <div :class="{'invalid':!thirdInputValid}" class="warning" v-show="(numberOfInputs.number >= 4 && isUserCreating) || (numberOfInputs.number >= 3 && !isUserCreating)">
          <input ref="thirdInput" v-model="thirdInput" :disabled="disabledThirdInput"
                 :placeholder="inputText.third"
                 :type="typeThirdInput"
                 class="pretty-input"
                 :class="{'answer-input':isUserCreated}"
                 maxlength="50"
                 minlength="3">
          <span :data-validate="errors.thirdInput"></span>
        </div>
      </div>
      <button :class="{'red-button': this.isUserDeleting || this.isUserPasswordResetting}" class="my-button pretty-input confirm-button clickable"
              type="submit">
        <span class="disable-selection-text">
          {{ submitButtonText.third }}
          <br>
          {{ submitButtonText.first }}
          <br>
          {{ submitButtonText.second }}
          <br>
          {{ submitButtonText.fourth }}
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
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';

export default {
  name: "Auth",
  components:{
    vSelect
  },
  props: {

    authCode: {
      type: Number,
      default: 0,
      required: false
    },
    user: Object,
  },
  data() {
    return {
      errors: {
        firstInput: '',
        secondInput: '',
        selectInputs: '',
        thirdInput: ''
      },
      response: {
        status: null,
        message: ''
      },
      isLoading: false,
      isUserCreated: false,
      hasAlreadyReset: false,
      success: '',
      firstInput: null,
      secondInput: null,
      selectInput: null,
      thirdInput: null,
      firstInputValid: true,
      secondInputValid: true,
      selectInputValid: true,
      thirdInputValid: true
    }
  },
  computed: {
    numberOfInputs() {
      let classText
      let number
      if (this.isUserCreated) {
        classText = 'four-inputs'
        number = 4
      }
      else if (this.isChangingPassword || this.isUserCreating) {
        classText = 'three-inputs'
        number = 3
      } else if (this.isAuth || this.hasAlreadyReset) {
        classText = 'two-inputs'
        number = 2
      } else if (this.isUserPasswordResetting || this.isUserDeleting) {
        classText = 'one-input'
        number = 1
      }
      return {classText, number}
    },
    isAuth() {
      return this.authCode === this.$mydata.LOCAL_AUTH_CODES.LOGIN
    },
    isChangingPassword() {
      return this.authCode === this.$mydata.LOCAL_AUTH_CODES.CHANGING_PASSWORD
    },
    isUserCreating() {
      return this.authCode === this.$mydata.LOCAL_AUTH_CODES.CREATE_USER
    },
    isUserPasswordResetting() {
      return this.authCode === this.$mydata.LOCAL_AUTH_CODES.RESET_PASSWORD_USER
    },
    isUserDeleting() {
      return this.authCode === this.$mydata.LOCAL_AUTH_CODES.DELETE_USER
    },
    viewText() {
      let text = ''
      if (this.isAuth) {
        text = 'Вход'
      } else if (this.isChangingPassword) {
        text = 'Изменение пароля'
      } else if (this.isUserCreating) {
        text = 'Создание нового пользователя'
      } else if (this.isUserPasswordResetting) {
        text = 'Сброс пароля пользователя'
      } else if (this.isUserDeleting) {
        text = 'Удаление пользователя'
      }
      return text
    },
    inputText() {
      let first, second, third, select
      if (this.isAuth) {
        first = 'Имя'
        second = 'Пароль'
        third = ''
      } else if (this.isChangingPassword) {
        first = 'Старый пароль'
        second = 'Новый пароль'
        third = 'Повторите новый пароль'
      } else if (this.isUserCreating) {
        first = 'Имя'
        second = 'Краткое описание'
        third = 'Пароль'
      } else if (this.isUserPasswordResetting) {
        first = 'Имя'
        second = 'Новый пароль'
      } else if (this.isUserDeleting) {
        first = 'Имя'
      }
      select = 'Роль'
      return {
        first,
        second,
        third,
        select
      }
    },
    typeSecondInput() {
      let type = 'password'
      if (this.isUserCreating || this.isUserPasswordResetting)
        type = 'text'
      return type
    },
    typeThirdInput() {
      let type = 'password'
      if (this.isUserCreating)
        type = 'text'
      return type
    },
    submitButtonText() {
      let first, second, third, fourth
      if (this.isLoading) {
        first = 'Ожидайте...'
        second = 'Ожидайте...'
        third = 'Ожидайте...'
        fourth = 'Ожидайте...'
      } else if (this.isAuth) {
        first = 'Войти'
      } else if (this.isChangingPassword) {
        second = 'Изменить'
      } else if (this.isUserCreating) {
        second = 'Создать и получить пароль'
        fourth = 'Скопировать пароль'
      } else if (this.isUserPasswordResetting) {
        third = 'Сбосить и получить новый'
        first = 'Скопировать новый пароль'
      } else if (this.isUserDeleting) {
        third = 'Удалить'
      }
      return {
        first,
        second,
        third,
        fourth
      }
    },
    serverActionName() {
      let action
      if (this.isAuth)
        action = this.$mydata.server.action.LOGIN
      else if (this.isChangingPassword)
        action = this.$mydata.server.action.CHANGING_PASSWORD
      else if (this.isUserCreating)
        action = this.$mydata.server.action.CREATE_USER
      else if (this.isUserPasswordResetting)
        action = this.$mydata.server.action.RESET_USER_PASSWORD
      else if (this.isUserDeleting)
        action = this.$mydata.server.action.DELETE_USER
      return action
    },
    disabledFirstInput() {
      return this.isLoading || this.isUserCreated || this.isUserPasswordResetting || this.isUserDeleting
    },
    disabledSecondInput() {
      return this.isLoading || this.isUserCreated
    },
    disabledThirdInput() {
      return this.isLoading || this.isUserCreated
    },
    disabledSelectInput(){
      return false
    }
  },
  watch: {
    authCode() {
      this.clearForm()
    },
  },
  methods: {
    click(e) {
      if (this.$route.name!== 'auth' && !(this.$refs['form'] === e.target || this.$refs['form'].contains(e.target))) {
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
    createUserRequest() {
      if (!this.isUserCreated) {
        this.isLoading = true
        this.request().then(result => {
          this.isLoading = false
          if (result) {
            this.setMessage('Успешно!', true)
            this.thirdInput = result.password
            this.isUserCreated = true
          }
        })
      } else {
        this.copyStrToClipboard(this.thirdInput)
        this.setMessage('Скопировано!', true)
      }
    },
    loginRequest() {
      this.request().then(result => {
        if (result) {
          this.$emit('successful-login', this.firstInput)
          this.$router.push({name: 'profile'})
        }

      })
    },
    changePasswordRequest() {
      this.request().then(result => {
        if (result) {
          this.setMessage('Успешно!', true)
        }

      })
    },
    userResetRequest() {
      if (!this.hasAlreadyReset) {
        this.isLoading = true
        this.request().then(result => {
          this.isLoading = false
          if (result) {
            this.setMessage('Успешно!', true)
            this.secondInput = result.password
            this.hasAlreadyReset = true
          }
        })
      } else {
        this.copyStrToClipboard(this.secondInput)
        this.setMessage('Скопировано!', true)
      }
    },
    userDeleteRequest() {
      this.request().then(result => {
        if (result) {
          this.setMessage('Успешно!', true)
        }

      })
    },
    request() {
      let url = ''
      let data = {}
      if (this.isAuth) {
        url = this.$mydata.server.URL.auth
        data.login = this.firstInput
        data.password = this.secondInput
      } else if (this.isChangingPassword) {
        url = this.$mydata.server.URL.changePassword
        data.login = this.$mydata.currentName()
        data.oldPassword = this.firstInput
        data.newPassword = this.secondInput
      } else if (this.isUserCreating) {
        url = this.$mydata.server.URL.admin
        data.login = this.firstInput
        data.description = this.secondInput
        data.role_id = this.selectInput.id
      } else if (this.isUserPasswordResetting || this.isUserDeleting) {
        url = this.$mydata.server.URL.admin
        data.user_id = this.user.id
        data.login = this.user.login
      }
      data.purpose = this.serverActionName
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
            return response.data.data ? response.data.data : true
          }
        } else throw response

      }).catch(error => {
        console.log(error)
        console.log(error.response)
        if (error.code) {
          this.response.status = error.code;
          this.setMessage(error.message, false)

        } else {
          //js error
          if (error.message === 'NetworkError') {
            this.setMessage('Нет соединения с интернетом', false)
          } else {
            const message = error.response.status === 403 ? 'Запрещено' : 'Ошибка сервера'
            this.setMessage(message, false)
          }
        }
        return false
      })
    },
    checkForm() {
      const emptyText = 'Свято место пусто не бывает'
      const onlyLatin = 'Только латинские буквы и цифры'
      if (!this.firstInput) {

        this.firstInputValid = false;
        this.errors.firstInput = emptyText
      } else if (!this.firstInput.match(/^[A-Za-z0-9]*$/)) {
        this.firstInputValid = false;
        this.errors.firstInput = onlyLatin;
      } else {
        this.firstInputValid = true;
      }

      if (this.numberOfInputs.number > 1
          && !this.isUserPasswordResetting
          && !this.isUserDeleting
          && !this.hasAlreadyReset
          && !this.isUserCreated)
        if (!this.secondInput) {
          this.secondInputValid = false;
          this.errors.secondInput = emptyText
        } else if (!this.isUserCreating && !this.secondInput.match(/^[A-Za-z0-9]*$/)) {
          this.secondInputValid = false;
          this.errors.secondInput = onlyLatin;
        } else this.secondInputValid = true;
      else this.secondInputValid = true

      if (this.numberOfInputs.number > 2
          && !this.isUserPasswordResetting
          && !this.isUserDeleting
          && !this.hasAlreadyReset
          && !this.isAuth
          && !this.isUserCreating)
        if (!this.thirdInput) {
          this.thirdInputValid = false
          this.errors.secondInput = emptyText
        } else if (this.secondInput !== this.thirdInput) {

          this.thirdInputValid = false
          this.errors.thirdInput = 'Пароли не совпадают'
        } else this.thirdInputValid = true
      else this.thirdInputValid = true


      if(this.isUserCreating) {
        console.log(this.selectInput)
        if (!this.selectInput) {
          this.selectInputValid = false
          this.errors.selectInput = emptyText
        } else this.selectInputValid = true
      } else this.selectInputValid = true
       console.log(this.firstInputValid && this.secondInputValid && this.thirdInputValid && this.selectInputValid)
      console.log(this.firstInputValid)
      console.log(this.secondInputValid)
      console.log(this.thirdInputValid)
      if (this.firstInputValid && this.secondInputValid && this.thirdInputValid && this.selectInputValid) {
        if (this.isUserCreating)
          this.createUserRequest()
        else if (this.isAuth)
          this.loginRequest()
        else if (this.isChangingPassword)
          this.changePasswordRequest()
        else if (this.isUserPasswordResetting)
          this.userResetRequest()
        else if (this.isUserDeleting)
          this.userDeleteRequest()
      }
    },
    clearForm() {
      this.firstInputValid = true
      this.secondInputValid = true
      this.thirdInputValid = true
      this.isLoading = false
      this.isUserCreated = false
      this.hasAlreadyReset = false
      this.response.message = ''
      this.thirdInput = ''
      this.firstInput = ''
      this.secondInput = ''
      this.fillForm()
    },
    fillForm() {
      if (this.isUserPasswordResetting || this.isUserDeleting)
        this.firstInput = this.user.login
    },
    copyStrToClipboard(str) {
      const el = document.createElement('textarea');
      el.value = str;
      el.setAttribute('readonly', '');
      el.style.position = 'absolute';
      el.style.left = '-9999px';
      document.body.appendChild(el);
      el.select();
      document.execCommand('copy');
      document.body.removeChild(el);
    },
  },
  mounted() {
    this.clearForm()
    console.log('mounted')
  },
  beforeUpdate() {
    console.log('before update')

  }


}
</script>
<style>
.vs__search{
  margin: 0;
  padding: 0;
  overflow: hidden;
  background: #fff;
  border: 1px solid #fff;
  border-radius: 8px;
  font-size: 16px;
  height: 46px;
  opacity: 1;
  text-indent: 20px;
  transition: all .2s ease-in-out;
}
.vs__search:focus{
  margin: 0;
  padding: 0;
}
.vs__dropdown-toggle{
  background-color: white;
  padding: 0;
  border: 0;
}
.vs__actions:hover, .vs__actions:focus {
  cursor: pointer !important;
}
.v-select{
  width: 99.8%;
  border-radius: 8px;
}
.vs__dropdown-menu{
  border: 0;
}
.vs__dropdown-option--highlight{
  background-color: #348fe2;
}
.vs__actions{
 margin-right: 9px;
}
.vs__selected{
  padding: 0;
  margin-left: 20px;
  top: 8px;
}
.vs__search::placeholder{
  color: #7b7b7b;
}
.invalid .v-select {
  border: 1px solid #FF5B57;
  border-radius: 6px;
}

</style>
<style scoped>
.warning.select-box.invalid > span{
  background: none;
  margin-right: 16px;
}
.warning.select-box.invalid > span::after{
  right: 0;
  padding-right: 6px;
}
.form-inside-wrapper, label, .arrow, button span {
  transition: height, transform .5s, .5s ease, -webkit-transform .5s;
}

.form-inside-wrapper .arrow {
  left: 10px;
}
.one-input ~ .form-inside-wrapper {
  height: 68px;
}

.two-inputs ~ .form-inside-wrapper {
  height: 126px;
}

.two-inputs ~ button span {
  transform: translate3d(0, -48px, 0);
}

.three-inputs ~ .form-inside-wrapper {
  height: 184px;
}

.three-inputs ~ button span {
  transform: translate3d(0, -96px, 0);
}

.four-inputs ~ .form-inside-wrapper {
  height: 240px;
}

.four-inputs ~ button span {
  transform: translate3d(0, -144px, 0);
}
.answer-input{
  font-weight: bold;
}
form {
  max-width: 450px;
  width: 100%;
  min-width: 180px;
  margin: 0 15px;
}

.auth-name {
  display: inline-block;
  font-size: 16px;
  font-weight: 800;
  opacity: 1;
  margin-bottom: 15px;
  text-transform: uppercase;
}


.auth-name {
  margin-right: 20px;
}

button span {
  display: block;
  line-height: 48px;
  position: relative;
  top: 0;
  transform: translate3d(0, 0, 0);
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
  z-index: 2;
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

.confirm-button {
  text-indent: 0;
}
</style>