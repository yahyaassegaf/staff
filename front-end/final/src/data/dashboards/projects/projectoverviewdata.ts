import file1 from '/images/media/file-manager/1.png';
import file3 from '/images/media/file-manager/3.png';
import face1 from '/images/faces/1.jpg';
import face13 from '/images/faces/13.jpg';
import face14 from '/images/faces/14.jpg';

interface TaskType {
  id: number;
  title: string;
  liClass?: string;
}

interface BadgeType {
  id: number;
  label: string;
  className: string
}

interface FileDocumentType {
  id: number;
  name: string;
  size: string;
  img: string;
  avatarClass: string;
}

export interface Attachment {
  img: string;
  size: string;
}

export interface TimelineMessageType {
  id: number;
  avatar: string;
  name: string;
  role: string;
  time: string;
  message: string;
  attachment?: Attachment;
}


export interface TodoItemType {
  id: number;
  title: string;
  status: string;
  badgeClass: string;
  date: string;
  checked: boolean;
}


export const Tasks: TaskType[] = [
  { id: 1, title: 'Create wireframes for homepage', liClass: "" },
  { id: 2, title: 'Design product pages (UI)', liClass: "" },
  { id: 3, title: 'Design product pages (UI)' },
],
  Badges: BadgeType[] = [
    { id: 1, label: 'UI/UX Design', className: 'primary' },
    { id: 2, label: 'Front-End Development', className: 'secondary' },
    { id: 3, label: 'Back-End Development', className: 'warning' },
    { id: 4, label: 'Quality Assurance', className: 'info' },
    { id: 5, label: 'Project Management', className: 'success' },
    { id: 6, label: 'SEO Optimization', className: 'danger' },
  ],
  Filedocument: FileDocumentType[] = [
    { id: 1, name: 'Project_Proposal.pdf', size: '1.2MB', img: file1, avatarClass: 'p-2' },
    { id: 2, name: 'Design_Mockups.zip', size: '1.5MB', img: file3, avatarClass: '' },
    { id: 3, name: 'Project_Timeline.xlsx', size: '256KB', img: file1, avatarClass: 'p-2' },
    { id: 4, name: 'Contract_Agreement.pdf', size: '1.8MB', img: file1, avatarClass: 'p-2' },
  ],
  TimelineMessages: TimelineMessageType[] = [
    {
      id: 1,
      avatar: face13,
      name: "John",
      role: "Project Manager",
      time: "March 25, 2025 - 09:15 AM",
      message:
        "Hey team, I think we’re all set to begin with the initial setup for this project. We need to start by finalizing the tech stack and setting up the development environment. Does everyone have the required tools and access?",
    },
    {
      id: 2,
      avatar: face1,
      name: "Sara",
      role: "Frontend Developer",
      time: "March 25, 2025 - 09:30 AM",
      message:
        "Yes, I’ve installed the latest version of Node.js and set up my local environment. I’m ready to start with the frontend. Is React the final choice for the frontend framework?",
    },
    {
      id: 3,
      avatar: face13,
      name: "John",
      role: "Project Manager",
      time: "March 25, 2025 - 09:45 AM",
      message:
        "Yes, React it is! We’ll be using it for the UI components. Make sure to check the latest design files in the shared folder before starting the layout work.",
      attachment: {
        img: file3,
        size: "728.62KB",
      },
    },
    {
      id: 4,
      avatar: face14,
      name: "Mark",
      role: "Backend Developer",
      time: "March 25, 2025 - 10:00 AM",
      message:
        "I’ve got everything set up on my end. I’m ready to integrate with the backend APIs. Can you confirm if we are using Node.js for the backend or are we sticking to Laravel?",
    },
    {
      id: 5,
      avatar: face13,
      name: "Mark",
      role: "Backend Developer",
      time: "March 25, 2025 - 10:10 AM",
      message:
        "We’re going with Laravel for the backend, so you can go ahead and start with the API structure. I’ll make sure to share the database schema by the end of the day.",
    },
  ],
  TodoItems: TodoItemType[] = [
    {
      id: 1,
      title: "Follow up with client about design revisions",
      status: "Not Started",
      badgeClass: "info",
      date: "17,May",
      checked: true,
    },
    {
      id: 2,
      title: "Finalize website content",
      status: "Completed",
      badgeClass: "success",
      date: "17,May",
      checked: false,
    },
    {
      id: 3,
      title: "Test website for mobile responsiveness",
      status: "Not Started",
      badgeClass: "info",
      date: "18,May",
      checked: false,
    },
    {
      id: 4,
      title: "Set up database connection",
      status: "Pending",
      badgeClass: "warning",
      date: "19,May",
      checked: true,
    },
    {
      id: 5,
      title: "Complete user authentication system",
      status: "Not Started",
      badgeClass: "info",
      date: "21,May",
      checked: true,
    },
    {
      id: 6,
      title: "Prepare final demo for client approval",
      status: "Not Started",
      badgeClass: "info",
      date: "27,May",
      checked: true,
    },
  ];