<script lang="ts">
import { defineComponent, onMounted, ref, reactive } from "vue";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import { apiGet, apiPost } from "../../services/api/request";
import { BASE_URL } from "../../services/api/http";
import { getFotoUrl } from "../../utils/helpers";
import Pageheader from "../../shared/components/pageheader/pageheader.vue";

export default defineComponent({
  components: {
    Pageheader,
  },
  setup() {
    interface Level {
      id: string;
      nama: string;
    }
    interface Prodi {
      id: string;
      nama: string;
    }

    const levels = ref<Level[]>([]);
    const prodis = ref<Prodi[]>([]);
    const loading = ref(false);
    const uploading = ref(false);

    const user = ref<any>(null);
    const form = reactive({
      id: "",
      username: "",
      name: "",
      email: "",
      handphone: "",
      level_id: "",
      prodi_id: "",
      password: "",
      confirm_password: "",
    });

    const activeTab = ref("personal");

    async function getLevel() {
      try {
        const response = await apiGet("/get-level");
        if (response.success) {
          const data = response.data?.data;
          levels.value = Array.isArray(data) ? data : [data];
        }
      } catch (error) {
      }
    }

    async function getProdi() {
      try {
        const response = await apiGet("/get-prodi");
        if (response.success) {
          const data = response.data?.data;
          prodis.value = Array.isArray(data) ? data : [data];

          if (prodis.value.length === 1 && !form.prodi_id) {
            form.prodi_id = prodis.value[0].id;
          }
        }
      } catch (error) {
      }
    }

    async function getProfile() {
      try {
        loading.value = true;
        // Tambahkan timestamp agar browser tidak melakukan caching pada response profile
        const response = await apiGet("/profile", { _t: new Date().getTime() });
        if (response.success && response.data.status) {
          user.value = response.data.user;
          // Simpan foto secara global di localStorage agar komponen lain seperti sidebar bisa menggunakannya
          localStorage.setItem('userImage', user.value.img || "");
          window.dispatchEvent(new Event('profileUpdated'));
          
          Object.assign(form, {
            id: user.value.id,
            username: user.value.username,
            name: user.value.name,
            email: user.value.email,
            handphone: user.value.phone,
            level_id: user.value.level_id,
            prodi_id: user.value.prodi_id,
            password: "",
            confirm_password: "",
          });
        } else {
          toast.error("Gagal memuat profil");
        }
      } catch (error) {
        toast.error("Terjadi kesalahan saat memuat profil");
      } finally {
        loading.value = false;
      }
    }

    const selectedFile = ref<File | null>(null);
    const imageUrl = ref<string | null>(null);

    function onFileChange(e: Event) {
      const target = e.target as HTMLInputElement;
      if (target.files && target.files.length > 0) {
        selectedFile.value = target.files[0];
        imageUrl.value = URL.createObjectURL(selectedFile.value);
        updateProfileImage();
      }
    }

    async function updateProfileImage() {
      if (!selectedFile.value) return;

      const formData = new FormData();
      formData.append("foto", selectedFile.value);
      formData.append("name", form.name);
      formData.append("username", form.username);
      formData.append("email", form.email);
      formData.append("handphone", form.handphone);
      formData.append("_method", "PUT");

      try {
        uploading.value = true;
        const response = await apiPost("/profile", formData);
        if (response.success && response.data.status) {
          toast.success("Foto profil berhasil diperbarui");
          getProfile();
        } else {
          toast.error(
            response.data?.message || "Gagal memperbarui foto profil"
          );
        }
      } catch (error) {
        toast.error("Terjadi kesalahan saat mengunggah foto");
      } finally {
        uploading.value = false;
      }
    }

    async function submit() {
      // Basic Validation
      if (!form.name || !form.username || !form.email || !form.handphone) {
        toast.warning("Mohon lengkapkan semua field wajib");
        return;
      }

      if (form.password && form.password !== form.confirm_password) {
        toast.error("Konfirmasi password tidak cocok");
        return;
      }

      const formData = new FormData();
      formData.append("name", form.name);
      formData.append("username", form.username);
      formData.append("email", form.email);
      formData.append("handphone", form.handphone);
      formData.append("level_id", form.level_id);
      formData.append("prodi_id", form.prodi_id);
      formData.append("level_id", form.level_id);
      formData.append("prodi_id", form.prodi_id);

      if (form.password) {
        formData.append("password", form.password);
      }

      // Jika user memilih foto baru, ikutkan juga saat klik Simpan
      if (selectedFile.value) {
        formData.append("foto", selectedFile.value);
      }

      formData.append("_method", "PUT");

      try {
        loading.value = true;
        const response = await apiPost("/profile", formData);
        if (response.success && response.data.status) {
          toast.success(
            response.data.message || "Profile berhasil diperbarui",
            {
              theme: "auto",
              icon: true,
              autoClose: 2000,
            }
          );
          getProfile();
        } else {
          const errorMessage = response.data?.errors
            ? Object.values(response.data.errors).flat().join(", ")
            : response.data?.message || "Gagal memperbarui profil";
          toast.error(errorMessage);
        }
      } catch (error) {
        toast.error("Terjadi kesalahan saat mengupdate profile");
      } finally {
        loading.value = false;
      }
    }

    onMounted(() => {
      getLevel();
      getProdi();
      getProfile();
    });

    const dataToPass = {
      title: "User",
      currentpage: "Profile",
      activepage: "Profile Settings",
    };

    return {
      levels,
      prodis,
      user,
      form,
      selectedFile,
      imageUrl,
      onFileChange,
      submit,
      loading,
      uploading,
      activeTab,
      dataToPass,
      getFotoUrl,
    };
  },
});
</script>

<template>
  <Pageheader :propData="dataToPass" />

  <div class="container-fluid">
    <div class="row" v-if="user">
      <!-- Sidebar Profile -->
      <div class="col-xl-3 col-lg-4">
        <div class="card custom-card">
          <div class="card-body p-0">
            <div class="p-4 text-center border-bottom">
              <div class="mb-3 position-relative d-inline-block">
                <span class="avatar avatar-xxl avatar-rounded profile-img-main">
                  <img
                    :src="
                      imageUrl || getFotoUrl(user?.img)
                    "
                    alt="profile-img"
                  />
                </span>
                <label
                  for="profile-change"
                  class="position-absolute bottom-0 end-0 mb-1 me-1"
                >
                  <span
                    class="avatar avatar-sm avatar-rounded bg-primary text-fixed-white cursor-pointer shadow-sm"
                  >
                    <i class="ri-camera-line"></i>
                  </span>
                  <input
                    type="file"
                    id="profile-change"
                    class="d-none"
                    @change="onFileChange"
                    accept="image/*"
                  />
                </label>
                <div v-if="uploading" class="profile-img-loader">
                  <div
                    class="spinner-border spinner-border-sm text-primary"
                    role="status"
                  ></div>
                </div>
              </div>
              <h5 class="fw-semibold mb-1">{{ user.name }}</h5>
              <p class="text-muted mb-0 fs-13">{{ user.email }}</p>
              <div class="mt-3">
                <span class="badge bg-primary-transparent me-1">{{
                  user.level || "Staff"
                }}</span>
                <span class="badge bg-info-transparent">{{
                  user.prodi?.nama || "N/A"
                }}</span>
              </div>
            </div>
            <div class="p-3">
              <div
                class="nav flex-column nav-pills marketplace-nav"
                role="tablist"
              >
                <a
                  class="nav-link"
                  :class="{ active: activeTab === 'personal' }"
                  @click="activeTab = 'personal'"
                  href="javascript:void(0);"
                >
                  <i class="ri-user-line me-2 align-middle"></i>Biodata Diri
                </a>
                <a
                  class="nav-link"
                  :class="{ active: activeTab === 'contact' }"
                  @click="activeTab = 'contact'"
                  href="javascript:void(0);"
                >
                  <i class="ri-map-pin-line me-2 align-middle"></i>Alamat &
                  Kontak
                </a>
                <a
                  class="nav-link"
                  :class="{ active: activeTab === 'privacy' }"
                  @click="activeTab = 'privacy'"
                  href="javascript:void(0);"
                >
                  <i class="ri-shield-keyhole-line me-2 align-middle"></i
                  >Keamanan
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="col-xl-9 col-lg-8">
        <div class="card custom-card">
          <div class="card-header justify-content-between">
            <div class="card-title">
              {{
                activeTab === "personal"
                  ? "Biodata Diri"
                  : activeTab === "contact"
                  ? "Kontak"
                  : "Pengaturan Keamanan"
              }}
            </div>
          </div>
          <div class="card-body">
            <form @submit.prevent="submit">
              <!-- Personal Info Tab -->
              <div v-show="activeTab === 'personal'" class="tab-pane-content">
                <div class="row gy-4">
                  <div class="col-xl-12">
                    <label class="form-label text-muted fs-12 mb-1"
                      >Username</label
                    >
                    <input
                      type="text"
                      class="form-control form-control-lg border-light-dark"
                      v-model="form.username"
                      placeholder="Masukkan username"
                    />
                  </div>
                  <div class="col-xl-12">
                    <label class="form-label text-muted fs-12 mb-1"
                      >Nama Lengkap</label
                    >
                    <input
                      type="text"
                      class="form-control form-control-lg border-light-dark"
                      v-model="form.name"
                      placeholder="Masukkan nama lengkap"
                    />
                  </div>

                  <div class="col-xl-12">
                    <label class="form-label text-muted fs-12 mb-1"
                      >Program Studi</label
                    >
                    <select
                      class="form-select form-control-lg border-light-dark"
                      v-model="form.prodi_id"
                      disabled
                    >
                      <option value="">Pilih Prodi</option>
                      <option
                        v-for="prodi in prodis"
                        :key="prodi.id"
                        :value="prodi.id"
                      >
                        {{ prodi.nama }}
                      </option>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Contact Info Tab -->
              <div v-show="activeTab === 'contact'" class="tab-pane-content">
                <div class="row gy-4">
                  <div class="col-xl-12">
                    <label class="form-label text-muted fs-12 mb-1"
                      >Email</label
                    >
                    <div class="input-group">
                      <input
                        type="email"
                        class="form-control form-control-lg border-light-dark"
                        v-model="form.email"
                      />
                      <span
                        class="input-group-text bg-success-transparent text-success border-light-dark"
                        ><i class="ri-check-double-line me-1"></i
                        >Terverifikasi</span
                      >
                    </div>
                  </div>
                  <div class="col-xl-12">
                    <label class="form-label text-muted fs-12 mb-1"
                      >Nomor Handphone</label
                    >
                    <input
                      type="text"
                      class="form-control form-control-lg border-light-dark"
                      v-model="form.handphone"
                      placeholder="Contoh: 08123456789"
                    />
                  </div>
                </div>
              </div>

              <!-- Privacy Tab -->
              <div v-show="activeTab === 'privacy'" class="tab-pane-content">
                <div
                  class="alert alert-info d-flex align-items-center mb-4 border-0 shadow-none fs-13"
                  role="alert"
                >
                  <i class="ri-information-line me-2 fs-18"></i>
                  Gunakan password yang kuat dengan minimal 8 karakter kombinasi
                  huruf dan angka.
                </div>
                <div class="row gy-4">
                  <div class="col-xl-12">
                    <label class="form-label text-muted fs-12 mb-1"
                      >Password Baru</label
                    >
                    <input
                      type="password"
                      class="form-control form-control-lg border-light-dark"
                      v-model="form.password"
                      placeholder="Kosongkan jika tidak ingin diubah"
                    />
                  </div>
                  <div class="col-xl-12">
                    <label class="form-label text-muted fs-12 mb-1"
                      >Konfirmasi Password Baru</label
                    >
                    <input
                      type="password"
                      class="form-control form-control-lg border-light-dark"
                      v-model="form.confirm_password"
                      placeholder="Ulangi password baru"
                    />
                  </div>
                </div>
              </div>

              <!-- Save Button -->
              <div class="mt-5 pt-3 border-top d-flex justify-content-end">
                <button
                  type="submit"
                  class="btn btn-primary btn-lg px-5 shadow-sm"
                  :disabled="loading"
                >
                  <span
                    v-if="loading"
                    class="spinner-border spinner-border-sm me-2"
                    role="status"
                  ></span>
                  Simpan Perubahan
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <div v-else class="text-center p-5">
      <div class="spinner-grow text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>
  </div>
</template>

<style scoped>
.marketplace-nav .nav-link {
  color: #536485;
  padding: 12px 16px;
  border-radius: 8px;
  margin-bottom: 4px;
  font-weight: 500;
  transition: all 0.2s ease;
}

.marketplace-nav .nav-link:hover {
  background-color: #f3f6f8;
  color: #0d6efd;
}

.marketplace-nav .nav-link.active {
  background-color: #eef4ff;
  color: #0d6efd;
  font-weight: 600;
}

.profile-img-main {
  width: 120px;
  height: 120px;
  border: 4px solid #fff;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.profile-img-loader {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
}

.border-light-dark {
  border-color: #e9ecef;
}

.form-control-lg:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.05);
}

.tab-pane-content {
  animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(5px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
