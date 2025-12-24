import team1 from "/images/faces/team/1.png"
import team2 from "/images/faces/team/2.png"
import team3 from "/images/faces/team/3.png"
import team4 from "/images/faces/team/4.png"
import team5 from "/images/faces/team/5.png"
import team6 from "/images/faces/team/6.png"
import team7 from "/images/faces/team/7.png"
import team8 from "/images/faces/team/8.png"
import team9 from "/images/faces/team/9.png"
import team10 from "/images/faces/team/10.png"


interface TeamMemberType {
    id: number;
    imgSrc: string;
    name: string;
    role: string;
}

export const TeamMembers: TeamMemberType[] = [
    {
        id: 1,
        imgSrc: team1,
        name: "John Smith",
        role: "Senior Developer",
    },
    {
        id: 2,
        imgSrc: team2,
        name: "Emily Johnson",
        role: "Product Manager",
    },
    {
        id: 3,
        imgSrc: team3,
        name: "Sarah Davis",
        role: "Marketing Specialist",
    },
    {
        id: 4,
        imgSrc: team4,
        name: "Michael Brown",
        role: "Lead Designer",
    },
    {
        id: 5,
        imgSrc: team5,
        name: "Anna Martinez",
        role: "UX/UI Designer",
    },
    {
        id: 6,
        imgSrc: team6,
        name: "James Taylor",
        role: "Project Manager",
    },
    {
        id: 7,
        imgSrc: team7,
        name: "Olivia Harris",
        role: "Content Strategist",
    },
    {
        id: 8,
        imgSrc: team8,
        name: "Daniel Lee",
        role: "Software Engineer",
    },
    {
        id: 9,
        imgSrc: team9,
        name: "Mia White",
        role: "QA Engineer",
    },
    {
        id: 10,
        imgSrc: team10,
        name: "Ethan Scott",
        role: "Digital Marketing Lead",
    }
];