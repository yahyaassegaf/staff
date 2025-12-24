<script lang="ts" setup>
import { ref, onMounted } from "vue";
import Tagify from "@yaireo/tagify";
import "@yaireo/tagify/dist/tagify.css";
import Pageheader from "../../../shared/components/pageheader/pageheader.vue";

// Define page meta

// Refs for Tagify instances
const tagifyRef = ref<Tagify | null>(null);
const tagifyWishListRef = ref<Tagify | null>(null);
const dragSortRef = ref<Tagify | null>(null);

// Page header data
const dataToPass = {
  title: "Forms",
  currentpage: "Form Advanced",
  activepage: "Form Advanced",
};

// Initialize Tagify instances
onMounted(() => {
  const basicInput = document.querySelector<HTMLInputElement>('input[name="basic"]');
  if (basicInput) {
    tagifyRef.value = new Tagify(basicInput, {
      maxTags: 10,
      placeholder: "Add tags here...",
    });
  }

  const wishListInput = document.querySelector<HTMLInputElement>('input[name="input-custom-dropdown"]');
  if (wishListInput) {
    tagifyWishListRef.value = new Tagify(wishListInput, {
      maxTags: 10,
      whitelist: [
        "A# .NET", "A# (Axiom)", "A+", "A++", "ABAP", "ABC", "ABC ALGOL",
        "ABSET", "ABSYS", "ACC", "Accent", "Ace DASL", "ACL2", "Avicsoft",
        "ACT-III", "Action!", "ActionScript", "Ada", "Adenine", "Agda"
      ],
      placeholder: "write some tags",
      dropdown: {
        enabled: 0,
      }
    });
  }

  const dragSortInput = document.querySelector<HTMLInputElement>('input[name="drag-sort"]');
  if (dragSortInput) {
    dragSortRef.value = new Tagify(dragSortInput, {
      maxTags: 10,
      placeholder: "Add more tags...",
      dropdown: {
        enabled: 0,
      }
    });
  }
});
</script>


<template>
<Pageheader :propData="dataToPass" />

<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">
                    Tagify Js
                </div>
            </div>
            <div class="card-body">
                <div class="row gy-3">
                    <div class="col-xl-6">
                        <label class="form-label d-block">Basic Tagify</label>
                        <input name='basic' value='tag1, tag2' autofocus class="form-control">
                    </div>
                    <div class="col-xl-6">
                        <label class="form-label d-block">Tagify With Custom Suggestions</label>
                        <input name='input-custom-dropdown' class="form-control some_class_name" placeholder='write some tags' value='css, html, javascript'>
                    </div>
                    <div class="col-xl-6">
                        <label class="form-label d-block">Diasbled User Input</label>
                        <input name='tags-disabled-user-input' placeholder='Select tags from the list' class="form-control" disabled>
                    </div>
                    <div class="col-xl-6">
                        <label class="form-label d-block">Drag & Sort</label>
                        <input name='drag-sort' class="form-control" value='tag 1, tag 2, tag 3, tag 4, tag 5, tag 6'>
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
