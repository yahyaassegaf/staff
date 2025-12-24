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
import face13 from '/images/faces/13.jpg';
import face14 from '/images/faces/14.jpg';
import face15 from '/images/faces/15.jpg';
import face16 from '/images/faces/16.jpg';

interface ProfileType {
    name: string;
    mail: string;
    imgSrc: string;
    icon: string;
    color: string;
    followers: string
}

interface FriendType {
    name: string;
    mail: string;
    imgSrc: string;
}

export const Profiles: ProfileType[] = [
    {
        name: "JohnDoe",
        mail: "john.doe@example.com",
        imgSrc: face9,
        icon: "add",
        color: 'primary',
        followers: 'Follow'
    },
    {
        name: "SarahSmith",
        mail: "sarah.smith@example.com",
        imgSrc: face1,
        icon: "add",
        color: 'primary',
        followers: 'Follow'
    },
    {
        name: "MichaelBrown",
        mail: "michael.brown@example.com",
        imgSrc: face10,
        icon: "add",
        color: 'primary',
        followers: 'Follow'
    },
    {
        name: "EmmaWilson",
        mail: "emma.wilson@example.com",
        imgSrc: face2,
        icon: "add",
        color: 'primary',
        followers: 'Follow'
    },
    {
        name: "JamesTaylor",
        mail: "james.taylor@example.com",
        imgSrc: face11,
        icon: "minus",
        color: 'danger',
        followers: 'Unfollow'
    },
    {
        name: "OliviaJohnson",
        mail: "olivia.johnson@example.com",
        imgSrc: face3,
        icon: "add",
        color: 'primary',
        followers: 'Follow'
    },
    {
        name: "DavidMartinez",
        mail: "david.martinez@example.com",
        imgSrc: face13,
        icon: "add",
        color: 'primary',
        followers: 'Follow'
    },
    {
        name: "SophiaGarcia",
        mail: "sophia.garcia@example.com",
        imgSrc: face4,
        icon: "add",
        color: 'primary',
        followers: 'Follow'
    },
    {
        name: "DanielLee",
        mail: "daniel.lee@example.com",
        imgSrc: face14,
        icon: "add",
        color: 'primary',
        followers: 'Follow'
    },
    {
        name: "IsabellaHarris",
        mail: "isabella.harris@example.com",
        imgSrc: face5,
        icon: "minus",
        color: 'danger',
        followers: 'Unfollow'
    },
    {
        name: "WilliamClark",
        mail: "william.clark@example.com",
        imgSrc: face15,
        icon: "add",
        color: 'primary',
        followers: 'Follow'
    },
    {
        name: "MiaLewis",
        mail: "mia.lewis@example.com",
        imgSrc: face6,
        icon: "add",
        color: 'primary',
        followers: 'Follow'
    },
    {
        name: "AlexanderWalker",
        mail: "alexander.walker@example.com",
        imgSrc: face16,
        icon: "add",
        color: 'primary',
        followers: 'Follow'
    },
    {
        name: "CharlotteAllen",
        mail: "charlotte.allen@example.com",
        imgSrc: face7,
        icon: "add",
        color: 'primary',
        followers: 'Follow'
    },
    {
        name: "BenjaminYoung",
        mail: "benjamin.young@example.com",
        imgSrc: face8,
        icon: "minus",
        color: 'danger',
        followers: 'Unfollow'
    },
],
    FriendsList: FriendType[] = [
        {
            name: "JohnDoe",
            mail: "john.doe@example.com",
            imgSrc: face9,
        },
        {
            name: "SarahSmith",
            mail: "sarah.smith@example.com",
            imgSrc: face1,
        },
        {
            name: "MichaelBrown",
            mail: "michael.brown@example.com",
            imgSrc: face10,
        },
        {
            name: "EmmaWilson",
            mail: "emma.wilson@example.com",
            imgSrc: face2,
        },
        {
            name: "JamesTaylor",
            mail: "james.taylor@example.com",
            imgSrc: face11,
        },
        {
            name: "OliviaJohnson",
            mail: "olivia.johnson@example.com",
            imgSrc: face3,
        },
        {
            name: "DavidMartinez",
            mail: "david.martinez@example.com",
            imgSrc: face13
        },
        {
            name: "SophiaGarcia",
            mail: "sophia.garcia@example.com",
            imgSrc: face4
        },
        {
            name: "DanielLee",
            mail: "daniel.lee@example.com",
            imgSrc: face14
        },
        {
            name: "IsabellaHarris",
            mail: "isabella.harris@example.com",
            imgSrc: face6
        },
        {
            name: "WilliamClark",
            mail: "william.clark@example.com",
            imgSrc: face15
        },
        {
            name: "JohnDoe",
            mail: "john.doe@example.com",
            imgSrc: face9
        },
    ];