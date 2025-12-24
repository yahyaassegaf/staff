
import face1 from "/images/faces/1.jpg"
import face2 from "/images/faces/2.jpg"
import face3 from "/images/faces/3.jpg"
import face4 from "/images/faces/4.jpg"
import face5 from "/images/faces/5.jpg"
import face6 from "/images/faces/6.jpg"
import face7 from "/images/faces/7.jpg"
import face8 from "/images/faces/8.jpg"
import face9 from "/images/faces/9.jpg"
import face10 from "/images/faces/10.jpg"
import face12 from "/images/faces/12.jpg"
import face13 from "/images/faces/13.jpg"
import face14 from "/images/faces/14.jpg"
import face15 from "/images/faces/15.jpg"

interface AvatarDataType {
    id: number;
    class?: string;
    src: string;
}

interface AvatarIconType {
    id: number;
    class: string;
    src: string;
    icon: string;
    class1: string;
}

interface AvatarNumberType {
    id: number;
    class: string;
    src: string;
    icon: string;
    class1: string;
    data: string;
}

interface AvatarInitialType {
    id: number;
    data: string;
    color: string;
    data1: string;
}

export let
    Avatardata: AvatarDataType[] = [
        { id: 1, class: "avatar-radius-0", src: face1 },
        { id: 1, class: "", src: face2 },
        { id: 1, class: "avatar-rounded", src: face3 },
    ],
    Avataricon: AvatarIconType[] = [
        { id: 1, class: "xs", src: face2, icon: "camera", class1: "success" },
        { id: 2, class: "sm", src: face3, icon: "edit", class1: "secondary" },
        { id: 3, class: "md", src: face14, icon: "plus", class1: "warning" },
        { id: 4, class: "lg", src: face13, icon: "edit", class1: "info" },
        { id: 5, class: "xl", src: face15, icon: "camera", class1: "success" },
        { id: 6, class: "xxl", src: face9, icon: "plus", class1: "danger" },
    ],
    Avatarsize: AvatarDataType[] = [
        { id: 1, class: "xs", src: face4 },
        { id: 2, class: "sm", src: face5 },
        { id: 3, class: "md", src: face6 },
        { id: 4, class: "lg", src: face7 },
        { id: 5, class: "xl", src: face8 },
        { id: 6, class: "xxl", src: face9 },
    ],
    Avataronline: AvatarDataType[] = [
        { id: 1, class: "xs online", src: face8 },
        { id: 2, class: "sm online", src: face10 },
        { id: 3, class: "md online", src: face12 },
        { id: 4, class: "lg online", src: face13 },
        { id: 5, class: "xl online", src: face14 },
        { id: 6, class: "xxl online", src: face15 },
    ],
    Avataroffline: AvatarDataType[] = [
        { id: 1, class: "xs offline", src: face2 },
        { id: 2, class: "sm offline", src: face3 },
        { id: 3, class: "md offline", src: face4 },
        { id: 4, class: "lg offline", src: face5 },
        { id: 5, class: "xl offline", src: face6 },
        { id: 6, class: "xxl offline", src: face7 },
    ],
    Avatarnumber: AvatarNumberType[] = [
        { id: 1, class: "xs", src: face2, icon: "camera", class1: "primary", data: '2' },
        { id: 2, class: "sm", src: face3, icon: "edit", class1: "secondary", data: '5' },
        { id: 3, class: "md", src: face14, icon: "plus", class1: "warning", data: '1' },
        { id: 4, class: "lg", src: face13, icon: "edit", class1: "info", data: '7' },
        { id: 5, class: "xl", src: face15, icon: "camera", class1: "success", data: '3' },
        { id: 6, class: "xxl", src: face9, icon: "plus", class1: "danger", data: '9' },
    ],
    Avatarstack: AvatarDataType[] = [
        { id: 1, src: face2 },
        { id: 2, src: face8 },
        { id: 3, src: face2 },
        { id: 4, src: face10 },
        { id: 5, src: face4 },
        { id: 6, src: face13 },
    ],
    Avatarinitial: AvatarInitialType[] = [
        { id: 1, data: "xs", color: "primary", data1: "XS" },
        { id: 2, data: "sm", color: "secondary", data1: "SM" },
        { id: 3, data: "md", color: "warning", data1: "MD" },
        { id: 4, data: "lg", color: "danger", data1: "LG" },
        { id: 5, data: "xl", color: "success", data1: "XL" },
        { id: 6, data: "xxl", color: "info", data1: "XXL" },
    ]
