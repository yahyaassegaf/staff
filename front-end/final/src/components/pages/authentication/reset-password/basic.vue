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
                                <h4 class="mb-1 fw-semibold">Reset Password</h4>
                                <p class="mb-4 text-muted fw-normal">Set your new password here.</p>
                            </div>
                            <div class="row gy-3">
                                <div class="col-xl-12">
                                    <label for="reset-password" class="form-label text-default">Current Password</label>
                                    <div class="position-relative">
                                        <PasswordInput initialValue="" name="newpassword" id="newpassword"
                                            placeholder="current password" />
                                    </div>
                                </div>
                                <div class="col-xl-12">
                                    <label for="reset-newpassword" class="form-label text-default">New Password</label>
                                    <div class="position-relative">
                                        <PasswordInput initialValue="" name="newpassword" id="newpassword"
                                            placeholder="new password" />
                                    </div>
                                </div>
                                <div class="col-xl-12">
                                    <label for="reset-confirmpassword" class="form-label text-default">Confirm
                                        Password</label>
                                    <div class="position-relative">
                                        <PasswordInput initialValue="" name="newpassword" id="newpassword"
                                            placeholder="confirm password" />
                                    </div>
                                </div>
                            </div>
                            <div class="d-grid mt-3">
                                <router-link to="/dashboards/sales" class="btn btn-primary">Reset Password</router-link>
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
                                Dont want to reset? <router-link to="/pages/authentication/sign-in/basic"
                                    class="text-primary">login Here</router-link>
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
