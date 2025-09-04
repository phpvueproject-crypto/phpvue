<template>
    <div class="login-box">
        <i class="fa fa-times-circle fa-lg close-btn" title="關閉" @click="$emit('input', false)"/>
        <div class="login-box-title">
            <img src="/img/itri-logo.png" alt="ITRILogo">
            <span>工業技術研究院生醫與醫材研究所</span>
        </div>
        <div class="login-box-body">
            <p class="login-box-msg">登入</p>
            <form novalidate @submit.prevent="submit">
                <div class="form-group">
                    <input class="form-control input-frame" placeholder="請填寫帳號" v-model="form.account" name="account" v-validate="'required'">
                </div>
                <div class="text-left">
                    <span v-show="errors.has('account:required')" class="help is-danger text-left">帳號不能留空</span>
                </div>
                <div class="form-group">
                    <input type="password" class="form-control input-frame" placeholder="請填寫密碼" v-model="form.password" name="password" v-validate="'required|min:8'">
                </div>
                <div class="text-left">
                    <span v-show="errors.has('password:required')" class="help is-danger">密碼不能留空</span>
                    <span v-show="errors.has('password:min')" class="help is-danger">密碼至少需填8碼</span>
                </div>
                <!--                TODO 暫時移除忘記密碼連結，之後可能會加回-->
                <!--                <div class="forget-pwd pull-left">-->
                <!--                    <a href="javascript:void(0)">-->
                <!--                        忘記密碼-->
                <!--                    </a>-->
                <!--                </div>-->
                <div class="text-left">
                    <span v-show="hasError" class="help is-danger">{{ errMsg }}</span>
                </div>
                <div class="col-xs-12 login-button-row">
                    <button class="btn btn-light-green btn-lg" :disabled="sending">
                        登 入
                    </button>
                </div>
                <div class="clearfix"/>
            </form>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import {mapGetters} from 'vuex';
import Echo from 'laravel-echo';

export default {
    name: "LoginModal",
    $_veeValidate: {
        validator: 'new'
    },
    props: {
        regionId: {
            type: Number,
            default: 1
        }
    },
    computed: {
        ...mapGetters({
            roles: 'user/roles',
            permissions: 'user/permissions'
        })
    },
    data() {
        return {
            sending: false,
            form: {
                account: null,
                password: null
            },
            errMsg: null,
            hasError: false
        };
    },
    methods: {
        async submit() {
            try {
                const isPass = await this.validate();
                if(!isPass)
                    return;

                this.sending = true;
                let res = await axios.post('/login', this.form);
                let data = res.data;
                if(data.status == 0) {
                    data = data.data;
                    await this.syncCsrfToken();
                    await this.syncUser();
                    window.Echo = new Echo({
                        broadcaster: 'socket.io',
                        host: `${window.location.hostname}:${window.echoPort}`
                    });
                    if(this.regionId) {
                        await this.$router.push(`/regionMgmts/${this.regionId}`);
                    } else if(localStorage.redirect) {
                        const redirect = localStorage.redirect;
                        delete localStorage.redirect;
                        await this.$router.push(redirect);
                    } else {
                        if(this.permissions.contains('user-role-manage')) {
                            await this.$router.push('/users');
                        } else {
                            await this.$router.push('/regionMgmts?is_deploy=1');
                        }
                    }
                } else if(data.status == -1) {
                    this.errMsg = '目前此帳號沒綁定任何身份，請聯繫管理者！';
                    this.hasError = true;
                } else if(data.status == -2) {
                    this.errMsg = '該帳號權限未開通或已被禁止登入！';
                    this.hasError = true;
                }
            } catch(err) {
                const status = err.response.status;
                if(status == '422') {
                    this.errMsg = '請檢查輸入的帳號密碼是否正確'
                    this.hasError = true;
                }
                this.guestRedirectHome(err);
            }
            this.sending = false;
        }
    }
}
</script>

<style lang="scss" scoped>
.login-box-title{
    background-color: #87b994;
    box-shadow:       5px 5px 15px 0 #808080;
    border-radius:    15px 15px 0 0;
    letter-spacing:   2px;
    padding:          30px;
    width:            450px;
    span{
        color:       white;
        font-size:   20px;
        font-weight: bold;
    }
    img{
        vertical-align: top;
        width:          25px;
        height:         25px;
    }
}
.login-box{
    position: relative;
    z-index:  1;
    width:    450px;
    margin:   11% auto;
}
.login-box-body{
    box-shadow:    5px 5px 15px 0 #808080;
    width:         450px;
    border-radius: 0 0 15px 15px;
    padding:       20px;
}
.login-box-msg{
    color:          black;
    font-weight:    bold;
    font-size:      24px;
    letter-spacing: 6px;
}
.input-frame{
    border-radius: 10px;
    font-size:     20px;
    height:        40px;
}
.is-danger{
    font-size: 15px;
    margin:    0 0 0 12px;
}
.form-group{
    margin-top:    15px;
    margin-bottom: 5px;
}
.forget-pwd{
    font-size:   14px;
    margin-top:  10px;
    margin-left: 5px;
    a{
        color: #6db880;
    }
}
.login-button-row{
    margin-top:    20px;
    margin-bottom: 20px
}
.btn-lg{
    border-radius: 10px;
    font-size:     24px;
    font-weight:   bold;
    padding:       4px 8px;
}
.close-btn{
    position: absolute;
    top:      -15px;
    right:    -15px;
    cursor:   pointer;
    color:    #808080;
}
</style>
