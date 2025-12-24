<script setup>
import { ref } from 'vue'
import * as ProjectList from '../../../data/dashboards/projects/projectlist'
import Pageheader from '../../../shared/components/pageheader/pageheader.vue'
import TableComponent from '../../../shared/components/@spk/table-reuseble/table-component.vue'

const dataToPass = {
    activepage: 'Projects List',
    title: 'Dashboards',
    subtitle: 'Projects',
    currentpage: 'Projects List',
}

const ProjectselectValue = ref('Sort By')
const Projectselectdata = ['A - Z', 'Date Added', 'Newest', 'Type',]
</script>

<template>
    <Pageheader :propData="dataToPass" />
    <!-- Start::row-1 -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 projectlist">
                        <div class="d-flex gap-1 project-list-main d-inline-flex text-nowrap flex-wrap flex-xxl-nowrap">
                            <router-link to="/dashboards/projects/create-project" class="btn btn-primary me-2"><i
                                    class="ri-add-line me-1 fw-medium align-middle"></i>New Project</router-link>
                            <VueMultiselect :searchable="true" :show-labels="false" name="choices-single-default"
                                id="choices-single-default" :multiple="false" v-model="ProjectselectValue"
                                :options="Projectselectdata" :taggable="false">
                            </VueMultiselect>
                        </div>
                        <div class="avatar-list-stacked">
                            <span class="avatar avatar-sm avatar-rounded"
                                v-for="(img, index) in ProjectList.AvatarImages" :key="index">
                                <img :src="img" alt="img">
                            </span>
                            <a class="avatar avatar-sm bg-primary avatar-rounded text-fixed-white"
                                href="javascript:void(0);">
                                +8
                            </a>
                        </div>
                        <div class="d-flex" role="search">
                            <input class="form-control me-2" type="search" placeholder="Search Project"
                                aria-label="Search">
                            <button class="btn btn-light" type="submit">Search</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End::row-1 -->

    <!-- Start::row-2 -->

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card overflow-hidden">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <TableComponent tableClass="table text-nowrap"
                            :headers="[{ text: 'Project Name', thClass: '' }, { text: 'Client Name', thClass: '' }, { text: 'Start Date', thClass: '' }, { text: 'End Date', thClass: '' }, { text: 'Status', thClass: '' }, { text: 'Budget (USD)', thClass: '' }, { text: 'Assigned Team', thClass: '' }, { text: 'Priority', thClass: '' }, { text: 'Actions', thClass: '' },]"
                            :rows="ProjectList.ProjectListData" v-slot:cell="{ row }">
                            <td :class="row.tdClass">{{ row.name }}</td>
                            <td :class="row.tdClass">
                                <div class="d-flex align-items-center gap-1">
                                    <div class="lh-1">
                                        <span class="avatar avatar-sm avatar-rounded">
                                            <img :src="row.companyLogo" alt="">
                                        </span>
                                    </div>
                                    <div>
                                        {{ row.companyName }}
                                    </div>
                                </div>
                            </td>
                            <td :class="row.tdClass">{{ row.startDate }}</td>
                            <td :class="row.tdClass">{{ row.endDate }}</td>
                            <td :class="row.tdClass"><span :class="`badge bg-${row.statucolor}`">{{ row.status }}</span>
                            </td>
                            <td :class="row.tdClass">{{ row.budget }}</td>
                            <td :class="row.tdClass">
                                <div class="avatar-list-stacked">
                                    <span class="avatar avatar-sm avatar-rounded" v-for="(img, index) in row.team"
                                        :key="index">
                                        <img :src="img" alt="img">
                                    </span>
                                    <a class="avatar avatar-sm bg-primary avatar-rounded text-fixed-white" href="javascript:void(0);" v-if="row.extraTeam > 0">
                                        +{{ row.extraTeam }}
                                    </a>
                                </div>
                            </td>
                            <td :class="row.tdClass"><span
                                    :class="`text-${row.PriorityColor} d-flex align-items-center`"><i
                                        class="ri-circle-fill me-1 fs-10 lh-1"></i>{{ row.priority }}</span></td>
                            <td :class="row.tdClass">
                                <div class="dropdown">
                                    <a aria-label="anchor" href="javascript:void(0);"
                                        class="btn btn-icon btn-sm btn-light" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <i class="ri-more-2-fill"></i>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item d-inline-flex align-items-center"
                                                href="javascript:void(0);"><i
                                                    class="ti ti-eye me-1 align-middle"></i>View</a></li>
                                        <li><a class="dropdown-item d-inline-flex align-items-center"
                                                href="javascript:void(0);"><i
                                                    class="ti ti-edit me-1 align-middle"></i>Edit</a></li>
                                        <li><a class="dropdown-item d-inline-flex align-items-center"
                                                href="javascript:void(0);"><i
                                                    class="ti ti-trash me-1 align-middle"></i>Delete</a></li>
                                    </ul>
                                </div>
                            </td>
                        </TableComponent>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End::row-2 -->
    <ul class="pagination justify-content-end">
        <li class="page-item disabled">
            <a class="page-link">Previous</a>
        </li>
        <li class="page-item"><a class="page-link" href="javascript:void(0);">1</a></li>
        <li class="page-item"><a class="page-link" href="javascript:void(0);">2</a></li>
        <li class="page-item">
            <a class="page-link" href="javascript:void(0);">Next</a>
        </li>
    </ul>
</template>

<style scoped>
/* Add your styles here */
</style>
