interface RibbonType {
    id: number
    color: string
    position: string
    class: string
    title: string
}
export let
    Ribbons2: RibbonType[] = [
        { id: 1, color: "primary", position: "left", class: "end", title: "Left" },
        { id: 2, color: "success", position: "right", class: "start", title: "Right" },
    ],
    Ribbons3: RibbonType[] = [
        { id: 1, color: 'success', position: "top-left", class: 'end', title: "Top Left" },
        { id: 2, color: 'secondary', position: "top-right", class: 'start', title: "Top Right" },
        { id: 3, color: 'info', position: "top-left", class: 'end', title: "Top Left" },
        { id: 4, color: 'warning', position: "top-right", class: 'start', title: "Top Right" },
    ],
    Ribbon4: RibbonType[] = [
        { id: 1, color: 'primary', position: "top-left", class: 'end', title: "Top Left" },
        { id: 2, color: 'secondary', position: "top-right", class: 'start', title: "Top Right" },
    ],
    Ribbon6: RibbonType[] = [
        { id: 1, color: 'primary', position: "left", class: 'end', title: "Top Left" },
        { id: 2, color: 'secondary', position: "right", class: 'start', title: "Top Right" },
    ]