<script lang="ts" setup>
import {
    onMounted
} from 'vue';
import { useRouter } from 'vue-router';
import PasswordInput from '../../../../shared/UI/passwordInput.vue';

const setBodyClass = (action: string) => {
    if (action === "add") {
        document.body.classList.add("authentication-background");
    } else {
        document.body.classList.remove("authentication-background");
    }
};

onMounted(() => {
    const router = useRouter();

    // Check if the user has visited before
    if (localStorage.getItem("visited") === "true") {
        setBodyClass("add");
    } else {
        setBodyClass("add");
        localStorage.setItem("visited", "true");
    }

    // Listen for route changes to remove class on navigation
    router.beforeEach(() => {
        setBodyClass("remove");
    });

    const handleBeforeUnload = () => {
        setBodyClass("remove");
        localStorage.removeItem("visited");
    };

    window.addEventListener("beforeunload", handleBeforeUnload, {
        passive: true
    });

    return () => {
        window.removeEventListener("beforeunload", handleBeforeUnload);
        setBodyClass("remove");
    };
});

</script>

<template>
   
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
                            <h4 class="mb-1 fw-semibold">Create Password</h4>
                            <p class="mb-4 text-muted fw-normal">Set your new password</p>
                        </div>
                        <div class="row gy-3">
                            <div class="col-xl-12">
                                <label for="create-password" class="form-label text-default">Password</label>
                                <div class="position-relative">
                                    <PasswordInput initialValue="" name="newpassword" id="newpassword"
                                        placeholder="password" />
                                </div>
                            </div>
                            <div class="col-xl-12">
                                <label for="create-confirmpassword" class="form-label text-default">Confirm
                                    Password</label>
                                <div class="position-relative">
                                    <PasswordInput initialValue="" name="newpassword1" id="newpassword1"
                                        placeholder="password" />
                                </div>
                                <div class="mt-2">
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox" value="" id="defaultCheck1"
                                            checked>
                                        <label class="form-check-label" for="defaultCheck1">
                                            Remember password ?
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-grid mt-3">
                            <router-link to="/dashboards/sales" class="btn btn-primary">Create Password</router-link>
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
</template>

<style scoped>
/* Add your styles here */
</style>
