import face1 from '/images/faces/1.jpg';
import face3 from '/images/faces/3.jpg';
import face4 from '/images/faces/4.jpg';
import face6 from '/images/faces/6.jpg';
import face7 from '/images/faces/7.jpg';
import face10 from '/images/faces/10.jpg';
import face11 from '/images/faces/11.jpg';
import face13 from '/images/faces/13.jpg';
import face14 from '/images/faces/14.jpg';
import face15 from '/images/faces/15.jpg';

interface Skill {
    name: string;
    color: string;
}
interface PersonType {
    id: number;
    name: string;
    role: string;
    location: string;
    experience: string;
    education: string;
    skills: Skill[];
    employmentType: string;
    salaryRange: string;
    noticePeriod: string;
    imageUrl: string;
}

export const Peopletable: PersonType[] = [
    {
        id: 1,
        name: "John Doe",
        role: "Software Engineer",
        location: "New York, NY",
        experience: "5 Years",
        education: "B.Tech in CS",
        skills: [
            { name: "JavaScript", color: "primary" },
            { name: "React", color: "info" },
            { name: "Node.js", color: "warning" },
        ],
        employmentType: "Full-Time",
        salaryRange: "$70,000 - $90,000",
        noticePeriod: "2 Weeks",
        imageUrl: face10,
    },
    {
        id: 2,
        name: "Alice Smith",
        role: "Data Analyst",
        location: "London, UK",
        experience: "3 Years",
        education: "M.Sc in Data Science",
        skills: [
            { name: "Python", color: "pink" },
            { name: "SQL", color: "info" },
            { name: "Tableau", color: "warning" },
        ],
        employmentType: "Full-Time",
        salaryRange: "$60,000 - $75,000",
        noticePeriod: "success",
        imageUrl: face1,
    },
    {
        id: 3,
        name: "Mark Johnson",
        role: "Product Manager",
        location: "San Francisco, CA",
        experience: "6 Years",
        education: "MBA in Marketing",
        skills: [
            { name: "Product Management", color: "teal" },
            { name: "Agile", color: "warning" },
            { name: "Scrum", color: "danger" },
        ],
        employmentType: "Full-Time",
        salaryRange: "$100,000 - $120,000",
        noticePeriod: "1 Month",
        imageUrl: face11,
    },
    {
        id: 4,
        name: "Sarah Williams",
        role: "UI/UX Designer",
        location: "Berlin, Germany",
        experience: "4 Years",
        education: "B.Des in Design",
        skills: [
            { name: "Figma", color: "primary" },
            { name: "Adobe XD", color: "pink" },
            { name: "HTML", color: "purple" },
        ],
        employmentType: "Full-Time",
        salaryRange: "$55,000 - $70,000",
        noticePeriod: "2 Weeks",
        imageUrl: face3,
    },
    {
        id: 5,
        name: "David Brown",
        role: "Marketing Specialist",
        location: "Tokyo, Japan",
        experience: "4 Years",
        education: "B.A. in Marketing",
        skills: [
            { name: "SEO", color: "success" },
            { name: "Google Analytics", color: "danger" },
        ],
        employmentType: "Full-Time",
        salaryRange: "$45,000 - $60,000",
        noticePeriod: "Immediate",
        imageUrl: face13,
    },
    {
        id: 6,
        name: "Emma White",
        role: "HR Manager",
        location: "Mumbai, India",
        experience: "7 Years",
        education: "MBA in HR",
        skills: [
            { name: "Recruitment", color: "primary" },
            { name: "Training", color: "pink" },
        ],
        employmentType: "Full-Time",
        salaryRange: "$65,000 - $85,000",
        noticePeriod: "1 Month",
        imageUrl: face4,
    },
    {
        id: 7,
        name: "James Green",
        role: "Full Stack Developer",
        location: "Singapore",
        experience: "5 Years",
        education: "B.Tech in IT",
        skills: [
            { name: "JavaScript", color: "primary" },
            { name: "Node.js", color: "secondary" },
            { name: "MongoDB", color: "info" },
        ],
        employmentType: "Full-Time",
        salaryRange: "$75,000 - $95,000",
        noticePeriod: "2 Weeks",
        imageUrl: face14,
    },
    {
        id: 8,
        name: "Linda Harris",
        role: "Network Administrator",
        location: "Toronto, Canada",
        experience: "3 Years",
        education: "B.Sc in Networking",
        skills: [
            { name: "Networking", color: "primary" },
            { name: "Cisco", color: "danger" },
            { name: "VPN", color: "teal" },
        ],
        employmentType: "Full-Time",
        salaryRange: "$50,000 - $70,000",
        noticePeriod: "Immediate",
        imageUrl: face6,
    },
    {
        id: 9,
        name: "Chris Black",
        role: "Sales Manager",
        location: "Paris, France",
        experience: "5 Years",
        education: "B.Com in Sales",
        skills: [
            { name: "Sales Strategy", color: "purple" },
            { name: "Negotiation", color: "orange" },
        ],
        employmentType: "Full-Time",
        salaryRange: "$60,000 - $80,000",
        noticePeriod: "1 Month",
        imageUrl: face15,
    },
    {
        id: 10,
        name: "Laura Adams",
        role: "Customer Support Lead",
        location: "Sydney, Australia",
        experience: "4 Years",
        education: "B.A. in Communication",
        skills: [
            { name: "Customer Service", color: "success" },
            { name: "CRM", color: "warning" },
        ],
        employmentType: "Full-Time",
        salaryRange: "$45,000 - $55,000",
        noticePeriod: "Immediate",
        imageUrl: face7,
    },
];