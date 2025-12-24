import comppanyLogo1 from '/images/company-logos/1.png';
import companylogo10 from '/images/company-logos/10.png';

interface ProjectCandidate {
    id: number;
    title: string;
    description: string;
    logoSrc: typeof comppanyLogo1;
    assignedDate: string;
    status: string;
    dueDate: string;
}

interface Certification {
    id: number;
    title: string;
    issuer: string;
    year: string;
    liclass?: string;
}

interface PreferenceType {
    id: number;
    label: string;
    value: string;
    liclass?: string;
}

interface PersonalInfoType {
    id: number;
    label: string;
    value: string;
    icon: string;
    valueClass?: boolean
}

interface SkillType {
    id: number;
    name: string;
    progress: number;
    color: string;
}

interface SocialLinkType {
    id: number;
    iconClass: string;
    buttonClass: string;
    title: string;
}

export const ProjectsCandidate: ProjectCandidate[] = [
    {
        id: 1,
        title: "Weather Forecast Web App",
        description: "A weather forecasting application using React and OpenWeatherMap API.",
        logoSrc: comppanyLogo1,
        assignedDate: "January 10, 2025",
        status: "Completed",
        dueDate: "June 15, 2025",
    },
    {
        id: 2,
        title: "E-Commerce Website",
        description: "Developed a full-stack e-commerce site using Node.js and MongoDB.",
        logoSrc: companylogo10,
        assignedDate: "March 1, 2025",
        status: "Completed",
        dueDate: "September 30, 2025",
    },
],
    Certifications: Certification[] = [
        {
            id: 1,
            title: "Certified JavaScript Developer",
            issuer: "Code Academy",
            year: "2015",

        },
        {
            id: 2,
            title: "Certified Scrum Master",
            issuer: "Scrum Alliance",
            year: "2017",
            liclass: "border-top-0"
        },
        {
            id: 3,
            title: "Certified Full-Stack Web Developer",
            issuer: "Code Academy",
            year: "2023",
            liclass: "border-top-0"
        },
        {
            id: 4,
            title: "AWS Certified Solutions Architect",
            issuer: "Web Services (AWS)",
            year: "2024",
            liclass: "border-top-0"
        },
    ],
    Preferences: PreferenceType[] = [
        { id: 1, label: "Preferred Job Type", value: "Full-Time" },
        { id: 2, label: "Preferred Salary", value: "$80,000 - $100,000 per year", liclass: "border-top-0" },
        { id: 3, label: "Availability", value: "Immediate", liclass: "border-top-0" },
        { id: 4, label: "Preferred Location", value: "Remote or New York", liclass: "border-top-0" },
    ],
    PersonalInfoList: PersonalInfoType[] = [
        { id: 1, label: 'Email', value: 'johndoe@example.com', icon: 'ti-mail', valueClass: false },
        { id: 2, label: 'Phone', value: '+1 234 567 8901', icon: 'ti-phone', valueClass: false },
        { id: 3, label: 'Gender', value: 'Male', icon: 'ti-gender-female', valueClass: false },
        { id: 4, label: 'Date of Birth', value: 'January 15, 1990', icon: 'ti-cake', valueClass: false },
        { id: 5, label: 'Nationality', value: 'American', icon: 'ti-flag', valueClass: false },
        { id: 6, label: 'Languages Known', value: 'English, Spanish', icon: 'ti-language', valueClass: false },
        { id: 7, label: 'Address', value: '1234 Elm Street, New York, 10001, United States', icon: 'ti-map-pin', valueClass: true },
    ],
    Skills: SkillType[] = [
        { id: 1, name: 'JavaScript', progress: 80, color: 'primary' },
        { id: 2, name: 'Python', progress: 64, color: 'warning' },
        { id: 3, name: 'React', progress: 53, color: 'success' },
        { id: 4, name: 'Node.js', progress: 90, color: 'info' },
        { id: 5, name: 'Mongo DB', progress: 35, color: 'danger' },
        { id: 6, name: 'My SQL', progress: 70, color: 'secondary' },
    ],
    SocialLinks: SocialLinkType[] = [
        {
            id: 1,
            iconClass: 'brand-dribbble',
            buttonClass: 'pink',
            title: 'Dribbble',
        },
        {
            id: 2,
            iconClass: 'brand-github',
            buttonClass: 'github',
            title: 'Github',
        },
        {
            id: 3,
            iconClass: 'brand-behance',
            buttonClass: 'primary',
            title: 'Behance',
        },
        {
            id: 4,
            iconClass: 'world',
            buttonClass: 'success',
            title: 'Web',
        },
        {
            id: 5,
            iconClass: 'brand-pinterest',
            buttonClass: 'danger',
            title: 'Pinterest',
        },
    ];