import face1 from '/images/faces/1.jpg';
import face2 from '/images/faces/2.jpg';
import face3 from '/images/faces/3.jpg';
import face4 from '/images/faces/4.jpg';
import face5 from '/images/faces/5.jpg';
import face6 from '/images/faces/6.jpg';
import face7 from '/images/faces/7.jpg';
import face8 from '/images/faces/8.jpg';
import face9 from '/images/faces/9.jpg';
import face10 from '/images/faces/10.jpg';
import face11 from '/images/faces/11.jpg';
import face12 from '/images/faces/12.jpg';
import face13 from '/images/faces/13.jpg';
import face14 from '/images/faces/14.jpg';
import media36 from '/images/media/media-36.jpg';
import media41 from '/images/media/media-41.jpg';
import media43 from '/images/media/media-43.jpg';
import media69 from '/images/media/media-69.jpg';

export interface KanbanCardBase {
    id?: number;
    createdDate?: string;
    daysLeft?: string;
    tags?: string[];
    title?: string;
    Content?: boolean;
    description?: string;
    imgSrc?: boolean;
    comments: string;
    likes: string;
    avatars: string[];
    src?: string;
    down?: boolean;
}

export type KanbanCardType = KanbanCardBase;

export type KanbanCardWarningType = KanbanCardBase & { description: string };


export const KanbanCards: KanbanCardType[] = [
    {
        id: 1,
        createdDate: "28 May",
        daysLeft: "2 days left",
        tags: ["#SPK - 11", "UI/UX"],
        title: "New Dashboard design.",
        Content: true,
        description:
            "Lorem ipsum dolor sit amet consectetur adipisicing elit, Nulla soluta consectetur sit amet elit dolor sit amet.",
        comments: "02",
        likes: "12",
        avatars: [
            face11,
            face12,
            face7,
            face8
        ]
    },
    {
        id: 2,
        createdDate: "30 May",
        daysLeft: "2 days left",
        tags: ["#SPK - 05", "Marketing", "Finance"],
        title: "Marketing next projects.",
        Content: true,
        description:
            "Lorem ipsum dolor sit amet consectetur adipisicing elit. Nulla soluta",
        comments: "08",
        likes: "40",
        avatars: [
            face13,
            face6
        ]
    },
    {
        id: 3,
        createdDate: "02 Jun",
        daysLeft: "1 days left",
        tags: ["#SPK - 08", "Designing"],
        title: "Design multi usage landing.",
        imgSrc: true,
        comments: "28",
        likes: "16",
        avatars: [
            face2,
            face8,
            face5,
            face10
        ],
        src: media36
    }
],
    kanbanCardswarning: KanbanCardType[] = [
        {
            id: 1,
            createdDate: "01 Jun",
            daysLeft: "10 days left",
            tags: ["#SPK - 07", "Admin", "Authentication"],
            title: "Adding Authentication Pages.",
            Content: true,
            description: "Lorem ipsum dolor sit amet consectetur adipisicing elit. Nulla soluta",
            comments: "02",
            likes: "06",
            avatars: [
                face9,
                face8,
                face14
            ]
        },
        {
            id: 2,
            createdDate: "05 Jun",
            daysLeft: "14 days left",
            tags: ["#SPK - 15", "Planning"],
            title: "New Project Discussion.",
            imgSrc: true,
            src: media41,
            description: "",
            comments: "06",
            likes: "17",
            avatars: [
                face2,
                face8,
                face5,
                face10
            ]
        }
    ],
    kanbanCardsinfo: KanbanCardType[] = [
        {
            id: 1,
            createdDate: "02 Jun",
            daysLeft: "5 days left",
            tags: ["#SPK - 13", "UI Design", "Development"],
            title: "Create Calendar & Mail pages.",
            Content: true,
            description: "Lorem ipsum dolor sit amet consectetur adipisicing elit. Nulla soluta",
            comments: "13",
            likes: "05",
            avatars: [
                face13,
                face6
            ]
        },
        {
            id: 2,
            createdDate: "03 Jun",
            daysLeft: "12 days left",
            Content: true,
            tags: ["#SPK - 09", "Product"],
            title: "Project design Figma,Sketch.",
            description: "Lorem ipsum dolor sit amet consectetur adipisicing elit. Nulla soluta",
            comments: "0",
            likes: "02",
            avatars: [
                face12
            ]
        },
       
    ],
    kanbanCardsdanger: KanbanCardType[] = [
        {
            id: 1,
            createdDate: '05 Jun',
            daysLeft: "14 days left",
            imgSrc: true,
            tags: ['#SPK - 15', 'Review'],
            title: 'Design Architecture strategy.',
            src: media43,
            likes: "09",
            comments: "35",
            avatars: [
                face3,
                face5,
                face7,
            ],
        },
    ],
    kanbanCardsuccess: KanbanCardType[] = [
        {
            id: 1,
            createdDate: '12 Jun',
            down: true,
            Content: true,
            tags: ['#SPK - 04', 'UI/UX'],
            title: 'Sash project update.',
            description: 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Nulla soluta',
            likes: "18",
            comments: "3",
            avatars: [face6, face13]
        },
        {
            id: 2,
            createdDate: '10 Jun',
            down: true,
            tags: ['#SPK - 10', 'Development'],
            title: 'React JS new version update.',
            Content: true,
            description: 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Nulla soluta',
            likes: "22",
            comments: "12",
            avatars: [face10, face11, face1]
        },
        {
            id: 2,
            createdDate: '07 Jun',
            down: true,
            tags: ['#SPK - 16', 'Discussion'],
            title: 'Project discussion with client.',
            imgSrc: true,
            src: media69,
            likes: "11",
            comments: "5",
            avatars: [face4]
        }
    ]