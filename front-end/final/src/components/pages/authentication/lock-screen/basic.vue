<script lang="ts" setup>
import { onMounted } from 'vue';
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
                            <p class="h4 mb-2 fw-semibold">Hello Tom Phillip</p>
                            <p class="mb-3 text-muted fw-normal">Welcome Back</p>
                            <div class="d-flex gap-2 align-items-center mb-3">
                                <div class="lh-1">
                                    <span class="avatar avatar-sm avatar-rounded">
                                        <img src="/images/faces/12.jpg" alt="">
                                    </span>
                                </div>
                                <div>
                                    <p class="mb-0 text-dark fw-medium">tomphillip32@gmail.com</p>
                                </div>
                            </div>
                            <div class="row gy-3">
                                <div class="col-xl-12 mb-2">
                                    <label for="lockscreen-password" class="form-label text-default">Password</label>
                                    <div class="position-relative">
                                        <PasswordInput initialValue="" name="newpassword" id="newpassword"
                                            placeholder="password" />
                                    </div>
                                </div>
                                <div class="col-xl-12 d-grid mt-2">
                                    <router-link to="/dashboards/sales"
                                        class="btn btn-lg btn-primary">Unlock</router-link>
                                </div>
                            </div>
                            <div class="text-center">
                                <p class="fw-medium mt-3 mb-0">Try unlock with <a class="text-success"
                                        href="javascript:void(0);"><u>Finger print</u></a> / <a class="text-success"
                                        href="javascript:void(0);"><u>Face Id</u></a></p>
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
