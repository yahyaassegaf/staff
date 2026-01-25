import $ from 'jquery';
if (typeof window !== 'undefined') {
    (window as any).$ = (window as any).jQuery = $;
}
export default $;
