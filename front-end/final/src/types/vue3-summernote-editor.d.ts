declare module 'vue3-summernote-editor' {
  import { DefineComponent } from 'vue'
  
  interface SummernoteConfig {
    height?: number
    placeholder?: string
    toolbar?: any[][]
    [key: string]: any
  }
  
  const SummernoteEditor: DefineComponent<{
    modelValue?: string
    config?: SummernoteConfig
  }>
  
  export default SummernoteEditor
}
