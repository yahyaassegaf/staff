<script setup>
defineProps({
    card: Object
})
</script>
<template>
    <div class="row">
        <div class="col-md-6" v-for="(job, index) in card.badges" :key="index">
            <div class="card custom-card featured-jobs">
                <div class="card-body">
                    <div class="btn-list float-end">
                        <a href="javascript:void(0);" class="avatar avatar-sm avatar-rounded bg-light text-default"
                            data-bs-toggle="tooltip" data-bs-placement="top" aria-label="Add to wishlist"
                            data-bs-original-title="Add to wishlist"><span><i class="bi bi-heart"></i></span></a>
                        <a href="javascript:void(0);" class="avatar avatar-sm avatar-rounded bg-warning-transparent"
                            data-bs-toggle="tooltip" data-bs-placement="top" aria-label="Featured Jobs"
                            data-bs-original-title="Featured Jobs"><span><i class="bi bi-star-fill"></i></span></a>
                    </div>
                    <div class="d-flex mb-3 flex-wrap gap-2">
                        <div>
                            <span class="avatar avatar-lg avatar-rounded bg-primary bg-opacity-10 border"
                                v-html="job.icon"></span>
                        </div>
                        <div class="ms-2">
                            <h5 class="fw-medium mb-0 d-flex align-items-center"><a href="javascript:void(0);"> {{
                                job.title }}</a></h5>
                            <a href="javascript:void(0);"><i class="bi bi-building"></i> {{ job.company }}</a>
                        </div>
                    </div>
                    <div class="popular-tags mb-3">
                        <a href="javascript:void(0);" class="badge rounded-pill bg-light text-default me-1"><i
                                class="bi bi-geo-alt text-muted "></i> {{ job.location }}</a>
                        <a href="javascript:void(0);" class="badge rounded-pill bg-light text-default me-1"><i
                                class="bi bi-mortarboard text-muted "></i> Graduate</a>
                        <a href="javascript:void(0);" class="badge rounded-pill bg-light text-default me-1"><i
                                class="bi bi-clock text-muted "></i> Min - 2Years</a>
                    </div>
                    <div class="d-flex justify-content-between flex-wrap">
                        <h6 class="fw-medium mb-0">{{ job.salary }}</h6>
                        <a href="javascript:void(0);" class="text-primary fw-medium fs-14">
                            Apply Now <i class="fe fe-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>