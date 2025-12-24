export let
    vue3CircleWithDisabledAlphaColorPicker = {
        script: `<color-picker v-model:pureColor="pureColor" shape="circle" format="rgb" pickerType="chrome"
                    useType="pure" :disableAlpha="true" />` },
    vue3GradientColorPicker = { script: ` <color-picker v-model:gradientColor="gradientColor" useType="both" lang="en" />` },
    vuetifyColorPicker = {
        script: `<input type="color" class="form-control form-control-color border-0" id="exampleColorInput" value="#136ad0"  title="Choose your color" />` };