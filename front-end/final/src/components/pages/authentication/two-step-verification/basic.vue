<script lang="ts" setup>


const valueChange = (nextInputId: string) => {
  const nextInput = document.getElementById(nextInputId) as HTMLInputElement | null
  if (nextInput) {
    nextInput.focus()
  }
}



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
                        <p class="h4 mb-2 fw-semibold">Verify Your Account</p>
                        <p class="mb-4 text-muted fw-normal">Enter the 4 digit code sent to the registered email Id.</p>
                        <div class="row gy-3">
                            <div class="col-xl-12 mb-2">
                                <div class="row">
                                    <div class="col-3">
                                        <input ref="one" type="text" class="form-control form-control-lg text-center" id="one" maxlength="1" @keyup="valueChange('two')">
                                    </div>
                                    <div class="col-3">
                                        <input ref="two" type="text" class="form-control form-control-lg text-center" id="two" maxlength="1" @keyup="valueChange('three')">
                                    </div>
                                    <div class="col-3">
                                        <input ref="three" type="text" class="form-control form-control-lg text-center" id="three" maxlength="1" @keyup="valueChange('four')">
                                    </div>
                                    <div class="col-3">
                                        <input ref="four"  type="text" class="form-control form-control-lg text-center" id="four" maxlength="1">
                                    </div>
                                </div>
                                <div class="form-check mt-3">
                                    <input class="form-check-input" type="checkbox" value="" id="defaultCheck1">
                                    <label class="form-check-label" for="defaultCheck1">
                                        Did not recieve a code ?<router-link to="/applications/email/mail-app" class="text-primary ms-2 d-inline-block fw-medium">Resend</router-link>
                                    </label>
                                </div>
                            </div>
                            <div class="col-xl-12 d-grid mt-3">
                                <router-link to="/dashboards/sales" class="btn btn-lg btn-primary">Verify</router-link>
                            </div>
                        </div>
                        <div class="text-center">
                            <p class="text-danger mt-3 mb-0 fw-medium"><sup><i class="ri-asterisk"></i></sup>Keep the verification code private!</p>
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
