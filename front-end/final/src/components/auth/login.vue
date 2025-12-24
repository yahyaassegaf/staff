<script lang="ts">
import { defineComponent, onMounted, ref } from "vue";
import { useRouter } from "vue-router";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import ParticlesJs from "../../shared/components/@spk/reuseble-plugin/particles-js.vue";
import PasswordInput from "../../shared/UI/passwordInput.vue";
import { useAuthStore } from "../../stores/auth";

export default defineComponent({
  components: {
    PasswordInput, // Uncomment and import if needed
    ParticlesJs,
  },

  setup() {
    const { authenticateUser } = useAuthStore(); // use authenticateUser action from  auth store

    const user: any = ref({
      username: "spruko@gmail.com",
      password: "spruko1234",
    });

    const router = useRouter();

    const login = async () => {
      let data = await authenticateUser(user.value);
      if (data.authenticated) {
        toast.success("Logged In", {
          theme: "auto",
          icon: true,
          hideProgressBar: true,
          autoClose: true,
          position: "top-right",
        });
        // setTimeout(() => {
        // router.push("dashboards/courses");
        // }, 300);
        console.log("NAVIGATE NOW");
         router.push('/dashboards/courses'); 
      } else {
        toast.error("Invalid credentials", {
          theme: "auto",
          icon: true,
          hideProgressBar: true,
          autoClose: true,
          position: "top-right",
        });
      }
    };

    const setBodyClass = (action: string) => {
      if (action === "add") {
        document.body.classList.add("authentication-background");
      } else {
        document.body.classList.remove("authentication-background");
      }
    };

    onMounted(() => {
      if (localStorage.getItem("visited") === "true") {
        setBodyClass("add");
      } else {
        setBodyClass("add");
        localStorage.setItem("visited", "true");
      }

      router.beforeEach(() => {
        setBodyClass("remove");
      });

      const handleBeforeUnload = () => {
        setBodyClass("remove");
        localStorage.removeItem("visited");
      };

      window.addEventListener("beforeunload", handleBeforeUnload, {
        passive: true,
      });

      return () => {
        window.removeEventListener("beforeunload", handleBeforeUnload);
        setBodyClass("remove");
      };
    });

    return {
      user,
      login,
    };
  },
});
</script>

<template>
  <ParticlesJs />
  <div class="container">
    <div
      class="row justify-content-center align-items-center authentication authentication-basic h-100"
    >
      <div class="col-xxl-4 col-xl-5 col-lg-6 col-md-6 col-sm-8 col-12">
        <div class="card custom-card border-0 my-4">
          <div class="card-body p-5">
            <div class="mb-4">
              <router-link to="/dashboards/sales">
                <img
                  src="/images/brand-logos/toggle-logo.png"
                  alt="logo"
                  class="desktop-dark"
                />
              </router-link>
            </div>
            <div>
              <h4 class="mb-1 fw-semibold">Login</h4>
              <p class="mb-4 text-muted fw-normal">
                input username dan password anda
              </p>
            </div>
            <div class="row gy-3">
              <div class="col-xl-12">
                <label for="signin-email" class="form-label text-default"
                  >Email</label
                >
                <input
                  type="text"
                  class="form-control"
                  id="signin-email"
                  v-model="user.username"
                  placeholder="Enter Email"
                />
              </div>
              <div class="col-xl-12 mb-2">
                <label
                  for="signin-password"
                  class="form-label text-default d-block"
                  >Password</label
                >
                <div class="position-relative">
                  <PasswordInput
                    :initialValue="user.password"
                    name="psw"
                    id="password"
                    placeholder="Password"
                    required
                  />
                </div>
                <div class="mt-3">
                  <div class="form-check custom-login">
                    <input
                      class="form-check-input"
                      type="checkbox"
                      value=""
                      id="defaultCheck1"
                      checked
                    />
                    <label class="form-check-label" for="defaultCheck1">
                      Remember me
                    </label>
                    <router-link
                      to="/pages/authentication/reset-password/basic"
                      class="float-end link-danger fw-medium fs-12"
                      >Forget password ?</router-link
                    >
                  </div>
                </div>
              </div>
            </div>
            <div class="d-grid mt-2">
              <!-- <router-link to="/dashboards/courses" class="btn btn-primary" @click.prevent="login">Sign
                                In</router-link> -->
              <button class="btn btn-primary w-100 mt-2" @click="login">
                Sign In
              </button>
            </div>
            <!-- <div class="text-center my-3 authentication-barrier">
                            <span class="op-4 fs-13">OR</span>
                        </div> -->
            <!-- <div class="d-grid mb-3">
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
                        </div> -->
            <!-- <div class="text-center mt-3 fw-medium">
                            Dont have an account? <router-link to="/pages/authentication/sign-up/basic"
                                class="text-primary">Register Here</router-link>
                        </div> -->
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
