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
import face15 from '/images/faces/15.jpg';
import face16 from '/images/faces/16.jpg';
import comppanyLogo1 from '/images/company-logos/1.png';
import comppanyLogo2 from '/images/company-logos/2.png';
import comppanyLogo3 from '/images/company-logos/3.png';
import comppanyLogo4 from '/images/company-logos/4.png';
import comppanyLogo5 from '/images/company-logos/5.png';
import comppanyLogo6 from '/images/company-logos/6.png';
import comppanyLogo7 from '/images/company-logos/7.png';
import comppanyLogo8 from '/images/company-logos/8.png';
import comppanyLogo9 from '/images/company-logos/9.png';
import comppanyLogo10 from '/images/company-logos/10.png';

interface ProjectType {
    id: number;
    name: string;
    companyLogo: string;
    companyName: string;
    startDate: string;
    endDate: string;
    status: string;
    statucolor: string;
    budget: string;
    team: string[];
    extraTeam: number;
    priority: string;
    PriorityColor: string;
    tdClass?: string;
}

export const ProjectListData: ProjectType[] = [
    {
        id: 1,
        name: "Website Redesign",
        companyLogo: comppanyLogo1,
        companyName: "ABC Corp",
        startDate: "2025-01-10",
        endDate: "2025-03-15",
        status: "In Progress",
        statucolor: "info-transparent",
        budget: "$15,000",
        team: [
            face2,
            face8,
            face2,
            face10,
        ],
        extraTeam: 2,
        priority: "High",
        PriorityColor: "danger"
    },
    {
        id: 2,
        name: "Mobile App",
        companyLogo: comppanyLogo2,
        companyName: "XYZ Ltd",
        startDate: "2025-02-01",
        endDate: "2025-04-20",
        status: "Completed",
        statucolor: "success-transparent",
        budget: "$25,000",
        team: [
            face12,
            face9,
            face11,
        ],
        extraTeam: 4,
        priority: "Medium",
        PriorityColor: "warning"
    },
    {
        id: 3,
        name: "E-commerce Platform",
        companyLogo: comppanyLogo3,
        companyName: "FutureTech",
        startDate: "2025-03-05",
        endDate: "2025-05-30",
        status: "In Progress",
        statucolor: "info-transparent",
        budget: "$50,000",
        team: [
            face5,
            face6,
        ],
        extraTeam: 1,
        priority: "High",
        PriorityColor: "danger"
    },
    {
        id: 4,
        name: "CRM System",
        companyLogo: comppanyLogo4,
        companyName: "Innovate Solutions",
        startDate: "2025-01-15",
        endDate: "2025-04-05",
        status: "Delayed",
        statucolor: "warning-transparent",
        budget: "$20,000",
        team: [
            face3,
            face9,
            face12,
            face11,
        ],
        extraTeam: 2,
        priority: "Low",
        PriorityColor: "primary"
    },
    {
        id: 5,
        name: "Content Management System",
        companyLogo: comppanyLogo5,
        companyName: "DesignWorks",
        startDate: "2025-02-20",
        endDate: "2025-05-01",
        status: "Completed",
        statucolor: "success-transparent",
        budget: "$18,000",
        team: [
            face10,
            face2,
            face1,
        ],
        extraTeam: 1,
        priority: "Medium",
        PriorityColor: "warning"
    },
    {
        id: 6,
        name: "Analytics Dashboard",
        companyLogo: comppanyLogo6,
        companyName: "GreenTech",
        startDate: "2025-03-01",
        endDate: "2025-06-15",
        status: "In Progress",
        statucolor: "info-transparent",
        budget: "$30,000",
        team: [
            face7,
            face13,
        ],
        extraTeam: 0,
        priority: "High",
        PriorityColor: "danger"
    },
    {
        id: 7,
        name: "AI Integration",
        companyLogo: comppanyLogo7,
        companyName: "Tech Innovations",
        startDate: "2025-03-10",
        endDate: "2025-07-01",
        status: "Not Started",
        statucolor: "light text-default",
        budget: "$40,000",
        team: [
            face5,
            face14,
            face15,
        ],
        extraTeam: 2,
        priority: "High",
        PriorityColor: "danger"
    },
    {
        id: 8,
        name: "SEO Optimization",
        companyLogo: comppanyLogo8,
        companyName: "Creativa Solutions",
        startDate: "2025-01-25",
        endDate: "2025-03-10",
        status: "Completed",
        statucolor: "success-transparent",
        budget: "$8,000",
        team: [
            face13,
            face16,
        ],
        extraTeam: 2,
        priority: "Medium",
        PriorityColor: "warning"
    },
    {
        id: 9,
        name: "HR Management System",
        companyLogo: comppanyLogo9,
        companyName: "Innovators Inc",
        startDate: "2025-04-01",
        endDate: "2025-07-01",
        status: "In Progress",
        statucolor: "info-transparent",
        budget: "$12,000",
        team: [
            face1,
            face15,
            face12,
            face8,
        ],
        extraTeam: 7,
        priority: "Medium",
        PriorityColor: "warning"
    },
    {
        id: 10,
        name: "Project Management Tool",
        companyLogo: comppanyLogo10,
        companyName: "GreenFuture",
        startDate: "2025-02-10",
        endDate: "2025-02-10",
        status: "Delayed",
        statucolor: "warning-transparent",
        budget: "$22,000",
        team: [
            face5,
            face11,
            face13,
        ],
        extraTeam: 4,
        priority: "Low",
        PriorityColor: "primary",
        tdClass: 'border-bottom-0'
    }
];

export const AvatarImages: string[] = [
    face1,
    face2,
    face8,
    face12,
    face10,
    face4,
    face5,
    face13
];

