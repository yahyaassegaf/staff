import face13 from "/images/faces/13.jpg";
import face6 from "/images/faces/6.jpg";
import face15 from "/images/faces/15.jpg";
import face5 from "/images/faces/5.jpg";
import face3 from "/images/faces/3.jpg";
import face1 from "/images/faces/1.jpg";
import face10 from "/images/faces/10.jpg";
import face14 from "/images/faces/14.jpg";
import face12 from "/images/faces/12.jpg";
import face11 from "/images/faces/11.jpg";
import face2 from "/images/faces/2.jpg";
import face8 from "/images/faces/8.jpg";
import face4 from "/images/faces/4.jpg";
import face16 from "/images/faces/16.jpg";

interface RowDataType {
    name: string;
    createdOn: string;
    number: string;
    status: string;
    color: string;
}

interface UserProfileType {
    name: string;
    status: string;
    email: string;
    avatar: string;
    color: string;
    textColor?: string;
}

interface BookingType {
    id: string;
    date: string;
    name: string;
    avatar: string;
    status: string;
}

interface ActiveTableRow {
    name: string;
    date: string;
    phone: string;
    status: string;
    statusClass: string;
    trClass?: string;
    tdClass?: string;
}



interface AlwaysResponsiveRowType {
    name: string;
    position: string;
    badgeClass: string;
    badgeText: string;
    email: string;
    avatar: string;
    collaborators: string[];
    progress: string;
    progressValue: number;
    salary: string;
    length: string;
}

interface DeliveryType {
    id: string;
    date: string;
    name: string;
    avatar: string;
    status: string;
    color: string;
}

interface ColorTableRowType {
    id: number;
    firstName: string;
    lastName: string;
    username: string;
}

interface ColorVariantTableRowType {
    type: string;
    title: string;
    status: string;
    badgeClass: string;
    quantity: number;
    amount: string;
    trClass: string;
}

interface ProductGroupType {
    name: string;
    brand: string;
    percentage: string;
    stock: string;
    color: string;
    dir: string;
}

interface HoverableRowsType {
    src: string;
    name: string;
    email: string;
    category: string;
    color: string;
    progress: number;
    avatars: string[];
    extraAvatarsCount: number;
}

interface HoverableRowType {
    number: string;
    name: string;
    email: string;
    status: string;
    statusClass: string;
    statusIcon: string;
    date: string;
    avatar: string;
}

interface SmallTablesProps {
    name: string;
    date: string;
    status: string;
    statusClass: string;
    cheacked: boolean;
}

interface StripedRowsProps {
    id: string;
    date: string;
    name: string;
}

interface TableFootRowType {
    id: string;
    location: string;
    count: number;
    percentage: string;
    trClass?: string;
}

interface OrderRowType {
    name: string;
    orderNumber: string;
    date: string;
    status: string;
    statusClass: string;
}

interface TableRowType {
    rank: string;
    country: string;
    year: number;
    value: number;
}

interface CompanyRowType {
    rank: string;
    name: string;
    revenue: string;
    country: string;
}

interface TicketRowType {
    name: string;
    ticketId: string;
    date: string;
    status: string;
    badgeClass: string;
}


export let
    basicRows: RowDataType[] = [
        { name: 'Mark', createdOn: '21, Dec 2021', number: '+1234-12340', status: 'Completed', color: "bg-outline-primary" },
        { name: 'Monika', createdOn: '29, April 2022', number: '+1523-12459', status: 'Failed', color: "bg-outline-warning" },
        { name: 'Madina', createdOn: '30, Nov 2022', number: '+1982-16234', status: 'Successful', color: "bg-outline-success" },
        { name: 'Bhamako', createdOn: '18, Mar 2022', number: '+1526-10729', status: 'Pending', color: "bg-outline-secondary" },
    ],
    bordered: UserProfileType[] = [
        { name: "Sukuro Kim", status: "Active", email: "kimosukuro@gmail.com", avatar: face13, color: "success-transparent" },
        { name: "Hasimna", status: "Inactive", email: "hasimna2132@gmail.com", avatar: face6, color: "light", textColor: "dark" },
        { name: "Azimo Khan", status: "Active", email: "azimokhan421@gmail.com", avatar: face15, color: "success-transparent" },
        { name: "Samantha Julia", status: "Active", email: "julianasams143@gmail.com", avatar: face5, color: "success-transparent" }
    ],
    borderedPrimary: BookingType[] = [
        { id: "#0007", date: "26-04-2022", name: "Mayor Kelly", avatar: face3, status: "Booked" },
        { id: "#0008", date: "15-02-2022", name: "Wicky Kross", avatar: face6, status: "Booked" },
        { id: "#0009", date: "23-05-2022", name: "Julia Cam", avatar: face1, status: "Booked" }
    ],
    activeTables: ActiveTableRow[] = [
        { name: 'Mark', date: '21, Dec 2021', phone: '+1234-12340', status: 'Completed', statusClass: 'bg-primary', trClass: "table-active" },
        { name: 'Monika', date: '29, April 2022', phone: '+1523-12459', status: 'Failed', statusClass: 'bg-warning', },
        { name: 'Madina', date: '30, Nov 2022', phone: '+1982-16234', status: 'Successful', statusClass: 'bg-success', tdClass: "table-active" },
        { name: 'Bhamako', date: '18, Mar 2022', phone: '+1526-10729', status: 'Pending', statusClass: 'bg-secondary', },
    ],
    alwaysResponsive: AlwaysResponsiveRowType[] = [
        { name: 'Mayor Kelly', position: 'Manufacturer', badgeClass: 'bg-primary-transparent', badgeText: 'Team Lead', email: 'mayorkrlly@gmail.com', avatar: face3, collaborators: [face2, face8, face2], progress: '52%', progressValue: 52, salary: '$10,984.29', length: '+4' },
        { name: 'Andrew Garfield', position: 'Managing Director', badgeClass: 'bg-warning-transparent', badgeText: 'Director', email: 'andrewgarfield@gmail.com', avatar: face12, collaborators: [face1, face5, face11, face15], progress: '91%', progressValue: 91, salary: '$1.4 billion', length: '+4' },
        { name: 'Simon Cowel', position: 'Service Manager', badgeClass: 'bg-success-transparent', badgeText: 'Manager', email: 'simoncowel234@gmail.com', avatar: face14, collaborators: [face6, face16], progress: '45%', progressValue: 45, salary: '$7,123.21', length: '+10' },
        { name: 'Mirinda Hers', position: 'Recruiter', badgeClass: 'bg-danger-transparent', badgeText: 'Employee', email: 'mirindahers@gmail.com', avatar: face5, collaborators: [face3, face10, face14], progress: '21%', progressValue: 21, salary: '$2,325.45', length: '+6' },
    ],
    borderedSuccess: DeliveryType[] = [
        { id: "#0011", date: "07-01-2022", name: "Helsenky", avatar: face10, status: "Delivered", color: "bg-success-transparent" },
        { id: "#0012", date: "18-05-2022", name: "Brodus", avatar: face14, status: "Delivered", color: "bg-success-transparent" },
        { id: "#0013", date: "19-03-2022", name: "Chikka Alen", avatar: face12, status: "Delivered", color: "bg-success-transparent" }
    ],
    borderedWarning: DeliveryType[] = [
        { id: "#0014", date: "21-02-2022", name: "Sukuro Kim", avatar: face13, status: "Accepted", color: "bg-warning-transparent" },
        { id: "#0018", date: "26-03-2022", name: "Alex Carey", avatar: face11, status: "Accepted", color: "bg-warning-transparent" },
        { id: "#0020", date: "14-03-2022", name: "Pamila Anderson", avatar: face2, status: "Accepted", color: "bg-warning-transparent" }
    ],
    colorTable: ColorTableRowType[] = [
        { id: 1, firstName: "Mark", lastName: "Otto", username: "@mdo" },
        { id: 2, firstName: "Jacob", lastName: "Thornton", username: "@fat" },
        { id: 3, firstName: "Larry the Bird", lastName: "Thornton", username: "@twitter" }
    ],
    colorvariantsTables: ColorVariantTableRowType[] = [
        { type: 'Default', title: 'Rita Book', status: 'Processed', badgeClass: 'bg-primary-transparent', quantity: 22, amount: '$2,012', trClass: '' },
        { type: 'Primary', title: 'Rhoda Report', status: 'Processed', badgeClass: 'bg-primary', quantity: 22, amount: '$4,254', trClass: 'table-primary' },
        { type: 'Secondary', title: 'Rita Book', status: 'Processed', badgeClass: 'bg-secondary', quantity: 26, amount: '$1,234', trClass: 'table-secondary' },
        { type: 'Success', title: 'Anne Teak', status: 'Processed', badgeClass: 'bg-success', quantity: 42, amount: '$2,623', trClass: 'table-success' },
        { type: 'Danger', title: 'Dee End', status: 'Processed', badgeClass: 'bg-danger', quantity: 52, amount: '$32,132', trClass: 'table-danger' },
        { type: 'Warning', title: 'Lee Nonmi', status: 'Processed', badgeClass: 'bg-warning', quantity: 10, amount: '$1,434', trClass: 'table-warning' },
        { type: 'Info', title: 'Lynne Gwistic', status: 'Processed', badgeClass: 'bg-info', quantity: 63, amount: '$1,854', trClass: 'table-info' },
        { type: 'Light', title: 'Fran Tick', status: 'Processed', badgeClass: 'bg-light text-dark', quantity: 5, amount: '$823', trClass: 'table-light' },
        { type: 'Dark', title: 'Polly Pipe', status: 'Processed', badgeClass: 'bg-dark text-white', quantity: 35, amount: '$1,832', trClass: 'table-dark' },
    ],
    groupDivideres: ProductGroupType[] = [
        { name: "Smart Watch", brand: "Slowtrack.inc", percentage: "24.23%", stock: "250/1786", color: "success", dir: "up" },
        { name: "White Sneakers", brand: "American & Co.inc", percentage: "12.45%", stock: "123/985", color: "danger", dir: "down" },
        { name: "Baseball Bat", brand: "Sports Company", percentage: "06.64%", stock: "124/232", color: "success", dir: "up" },
        { name: "Black Hoodie", brand: "Renolds Fabrics", percentage: "14.42%", stock: "192/2456", color: "success", dir: "up" }
    ],
    hoverableRows: HoverableRowsType[] = [
        { src: face10, name: "Joanna Smith", email: "joannasmith14@gmail.com", category: "Fashion", color: "bg-primary-transparent", progress: 52, avatars: [face10, face2, face8], extraAvatarsCount: 5, },
        { src: face2, name: "Kara Kova", email: "milesakara@gmail.com", category: "Clothing", color: "bg-warning-transparent", progress: 40, avatars: [face2, face4, face6], extraAvatarsCount: 6, },
        { src: face16, name: "Donald Trimb", email: "donaldo21@gmail.com", category: "Electronics", color: "bg-dark-transparent", progress: 17, avatars: [face16, face1, face11, face15], extraAvatarsCount: 2, },
        { src: face13, name: "Justin Gaethje", email: "justingae@gmail.com", category: "Sports", color: "bg-danger-transparent", progress: 72, avatars: [face13, face4, face6], extraAvatarsCount: 5, },
    ],
    hoverableRow: HoverableRowType[] = [
        { number: "IN-2032", name: "Mark Cruise", email: "markcruise24@gmail.com", status: "Paid", statusClass: "bg-success-transparent", statusIcon: "ri-check-fill", date: "Jul 26, 2022", avatar: face15, },
        { number: "IN-2022", name: "Charanjeep", email: "charanjeep@gmail.in", status: "Paid", statusClass: "bg-success-transparent", statusIcon: "ri-check-fill", date: "Mar 14, 2022", avatar: face12, },
        { number: "IN-2014", name: "Samantha Julie", email: "julie453@gmail.com", status: "Cancelled", statusClass: "bg-danger-transparent", statusIcon: "ri-close-fill", date: "Feb 1, 2022", avatar: face5, },
        { number: "IN-2036", name: "Simon Cohen", email: "simon@gmail.com", status: "Refunded", statusClass: "bg-light text-dark", statusIcon: "ri-reply-line", date: "Apr 24, 2022", avatar: face11, },
    ],
    smallTables: SmallTablesProps[] = [
        { name: 'Zelensky', date: '25-Apr-2021', status: 'Paid', statusClass: 'bg-success-transparent', cheacked: true },
        { name: 'Kim Jong', date: '29-Apr-2022', status: 'Pending', statusClass: 'bg-danger-transparent', cheacked: false },
        { name: 'Obana', date: '30-Nov-2022', status: 'Paid', statusClass: 'bg-success-transparent', cheacked: false },
        { name: 'Sean Paul', date: '01-Jan-2022', status: 'Paid', statusClass: 'bg-success-transparent', cheacked: false },
        { name: 'Karizma', date: '14-Feb-2022', status: 'Pending', statusClass: 'bg-danger-transparent', cheacked: false },
    ],
    stripedRows: StripedRowsProps[] = [
        { id: "2022R-01", date: "27-01-2022", name: "Moracco" },
        { id: "2022R-02", date: "28-10-2022", name: "Thornton" },
        { id: "2022R-03", date: "22-10-2022", name: "Larry Bird" },
        { id: "2022R-04", date: "29-09-2022", name: "Erica Sean" }
    ],
    tableFoot: TableFootRowType[] = [
        { id: '01', location: 'Manchester', count: 232, percentage: '42%' },
        { id: '02', location: 'Barcelona', count: 175, percentage: '58%' },
        { id: '03', location: 'Portugal', count: 126, percentage: '32%' },
        { id: "Total", location: 'United States', count: 558, percentage: '56%', trClass: 'table-primary' },
    ],
    tableHeadwarning: OrderRowType[] = [
        { name: "Harshrath", orderNumber: "#5182-3467", date: "24 May 2022", status: "Pending", statusClass: "primary-light" },
        { name: "Zozo Hadid", orderNumber: "#5182-3412", date: "02 July 2022", status: "Pending", statusClass: "primary-light" },
        { name: "Martiana", orderNumber: "#5182-3423", date: "15 April 2022", status: "Rejected", statusClass: "danger-light" },
        { name: "Alex Carey", orderNumber: "#5182-3456", date: "17 March 2022", status: "Processed", statusClass: "success-light" }
    ],
    tableWithCaption: TableRowType[] = [
        { rank: '01', country: 'United States', year: 2012, value: 1823 },
        { rank: '02', country: 'United Kingdom', year: 1012, value: 992 },
        { rank: '03', country: 'Germany', year: 914, value: 875 },
    ],
    tableWithTopCaption: CompanyRowType[] = [
        { rank: '1', name: 'Microsoft', revenue: '$170 billion', country: 'United States' },
        { rank: '2', name: 'HP', revenue: '$72 billion', country: 'United States' },
        { rank: '3', name: 'IBM', revenue: '$60 billion', country: 'United States' },
    ],
    withourBorder: TicketRowType[] = [
        { name: "Harshrath", ticketId: "#5182-3467", date: "24 May 2022", status: "Fixed", badgeClass: "bg-primary" },
        { name: "Zozo Hadid", ticketId: "#5182-3412", date: "02 July 2022", status: "In Progress", badgeClass: "bg-warning" },
        { name: "Martiana", ticketId: "#5182-3423", date: "15 April 2022", status: "Completed", badgeClass: "bg-success" },
        { name: "Alex Carey", ticketId: "#5182-3456", date: "17 March 2022", status: "Pending", badgeClass: "bg-danger" }
    ];