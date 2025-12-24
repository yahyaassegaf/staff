<script lang="ts" setup>
import {
    onBeforeUnmount,
    onMounted
} from 'vue';
import PasswordInput from '../../../../shared/UI/passwordInput.vue';
import { useRouter } from 'vue-router';

// Router instance
const router = useRouter()

// Function to move focus to next input
const valueChange = (nextInputId: string) => {
    const nextInput = document.getElementById(nextInputId) as HTMLInputElement | null
    if (nextInput) {
        nextInput.focus()
    }
}

// Add or remove background class
const setBodyClass = (action: 'add' | 'remove') => {
    if (action === 'add') {
        document.body.classList.add('bg-white')
    } else {
        document.body.classList.remove('bg-white')
    }
}

// Handle beforeunload cleanup
const handleBeforeUnload = () => {
    setBodyClass('remove')
    localStorage.removeItem('visited')
}

onMounted(() => {
    // On initial visit
    if (localStorage.getItem('visited') === 'true') {
        setBodyClass('add')
    } else {
        setBodyClass('add')
        localStorage.setItem('visited', 'true')
    }

    // Clean up before page reload
    window.addEventListener('beforeunload', handleBeforeUnload, { passive: true })

    // Remove class before route change
    router.beforeEach((to, from, next) => {
        setBodyClass('remove')
        next()
    })
})

onBeforeUnmount(() => {
    window.removeEventListener('beforeunload', handleBeforeUnload)
    setBodyClass('remove')
})
</script>

<template>
    <div class="row authentication authentication-cover-main mx-0">
        <div class="col-xxl-9 col-xl-9">
            <div class="row justify-content-center align-items-center h-100">
                <div class="col-xxl-4 col-xl-5 col-lg-6 col-md-6 col-sm-8 col-12">
                    <div class="card custom-card border-0 shadow-none my-4">
                        <div class="card-body p-5">
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
                                    <div class="position-relative custom-form">
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
        <div class="col-xxl-3 col-xl-3 col-lg-12 d-xl-block d-none px-0">
            <div class="authentication-cover overflow-hidden">
                <div class="authentication-cover-logo">
                    <router-link to="/dashboards/sales">
                        <img src="/images/brand-logos/toggle-logo.png" alt="logo" class="desktop-dark">
                    </router-link>
                </div>
                <div class="authentication-cover-background">
                    <img src="/images/media/backgrounds/9.png" alt="">
                </div>
                <div class="authentication-cover-content">
                    <div class="p-5">
                        <h3 class="fw-semibold lh-base">Welcome to Dashboard</h3>
                        <p class="mb-0 text-muted fw-medium">Manage your website and content with ease using our
                            powerful admin tools.</p>
                    </div>
                    <div>
                        <img src="/images/media/media-72.png" alt="" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Add your styles here */
</style>
