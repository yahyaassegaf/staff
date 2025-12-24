<script lang="ts" setup>
import PasswordInput from '../../../../shared/UI/passwordInput.vue';


</script>

<template>
    <div class="authentication-background">
        
        <div class="container">
            <div class="row justify-content-center align-items-center authentication authentication-basic h-100">
                <div class="col-xxl-4 col-xl-5 col-lg-6 col-md-6 col-sm-8 col-12">
                    <div class="card custom-card border-0 my-4">
                        <div class="card-body p-5">
                            <div class="mb-4">
                                <router-link to="/dashboards/sales">
                                    <img src="/images/brand-logos/toggle-logo.png" alt="logo" class="desktop-dark">
                                </router-link>
                            </div>
                            <div>
                                <h4 class="mb-1 fw-semibold">Hi,Welcome back!</h4>
                                <p class="mb-4 text-muted fw-normal">Please enter your credentials</p>
                            </div>
                            <div class="row gy-3">
                                <div class="col-xl-12">
                                    <label for="signin-email" class="form-label text-default">Email</label>
                                    <input type="text" class="form-control" id="signin-email" placeholder="Enter Email"
                                        value="tomphillip21@gmail.com">
                                </div>
                                <div class="col-xl-12 mb-2">
                                    <label for="signin-password"
                                        class="form-label text-default d-block">Password</label>
                                    <div class="position-relative">
                                        <PasswordInput initialValue="12345678" name="newpassword" id="newpassword"
                                            placeholder="Enter Password" />
                                    </div>
                                    <div class="mt-2">
                                        <div class="form-check custom-login">
                                            <input class="form-check-input" type="checkbox" value="" id="defaultCheck1"
                                                checked>
                                            <label class="form-check-label" for="defaultCheck1">
                                                Remember me
                                            </label>
                                            <router-link to="/pages/authentication/reset-password/basic"
                                                class="float-end link-danger fw-medium fs-12">Forget password
                                                ?</router-link>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-grid mt-3">
                                <router-link to="/dashboards/sales" class="btn btn-primary">Sign In</router-link>
                            </div>
                            <div class="text-center my-3 authentication-barrier">
                                <span class="op-4 fs-13">OR</span>
                            </div>
                            <div class="d-grid mb-3">
                                <button
                                    class="btn btn-white btn-w-lg border d-flex align-items-center justify-content-center flex-fill mb-3">
                                    <span class="avatar avatar-xs">
                                        <img src="/images/media/apps/google.png" alt="">
                                    </span>
                                    <span class="lh-1 ms-2 fs-13 text-default fw-medium">Signup with Google</span>
                                </button>
                                <button
                                    class="btn btn-white btn-w-lg border d-flex align-items-center justify-content-center flex-fill">
                                    <span class="avatar avatar-xs">
                                        <img src="/images/media/apps/facebook.png" alt="">
                                    </span>
                                    <span class="lh-1 ms-2 fs-13 text-default fw-medium">Signup with Facebook</span>
                                </button>
                            </div>
                            <div class="text-center mt-3 fw-medium">
                                Dont have an account? <router-link to="/pages/authentication/sign-up/basic"
                                    class="text-primary">Register Here</router-link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Add your styles here */
</style>
