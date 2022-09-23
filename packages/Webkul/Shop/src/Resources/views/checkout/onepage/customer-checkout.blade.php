<div v-if="is_customer_exist">
    <div class="control-group" id="password">
        <label for="password">{{ __('shop::app.checkout.onepage.password') }}</label>

        <input type="password" class="control" id="password" name="password" v-model="address.billing.password"/>
    </div>

    <div class="control-group" id="login-and-forgot-btn">
        <div class="forgot-password-link"  style="float: right; margin-right: 503px; margin-top: 11px;">
            <a href="{{ route('shop.customer.forgot_password.create') }}">{{ __('shop::app.customer.login-form.forgot_pass') }}</a>

            <div class="mt-10">
                @if (
                    Cookie::has('enable-resend')
                    && Cookie::get('enable-resend') == true
                )
                    <a href="{{ route('shop.customer.resend.verification_email', Cookie::get('email-for-resend')) }}">{{ __('shop::app.customer.login-form.resend_verification') }}</a>
                @endif
            </div>
        </div>

        <button type='button' id="" class="btn btn-primary btn-lg btn-login" @click="loginCustomer">
            {{ __('shop::app.customer.login-form.button_title') }}
        </button>
    </div>
</div>