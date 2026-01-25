<script lang="ts">
import { defineComponent, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../../stores/auth";

export default defineComponent({
  setup() {
    const { logoutUser } = useAuthStore();
    const router = useRouter();

    const logout = async () => {
      try {
        await logoutUser();
        router.push("/");
      } catch (error) {
        console.error("Logout error:", error);
        router.push("/");
      }
    };

    onMounted(() => {
      logout();
    });

    return {};
  },
});
</script>

<template>
  <div class="container">
    <div
      class="row justify-content-center align-items-center authentication authentication-basic h-100"
    >
      <div class="col-xxl-4 col-xl-5 col-lg-6 col-md-6 col-sm-8 col-12">
        <div class="card custom-card border-0 my-4">
          <div class="card-body p-5 text-center">
            <div class="mb-4">
              <h4 class="mb-1 fw-semibold">Logging Out</h4>
              <p class="mb-4 text-muted fw-normal">
                Please wait while we log you out...
              </p>
            </div>
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
