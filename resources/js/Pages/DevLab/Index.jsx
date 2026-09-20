import { Head } from "@inertiajs/react";
import AppLayout from "@/Layouts/AppLayout";
import DevLabShell from "@/Components/DevLab/DevLabShell";

export default function Index({ projects = [] }) {
    return <AppLayout><Head title="DevLab" /><div className="-mx-4 -my-5 sm:-mx-6 sm:-my-6 lg:-mx-8 lg:-my-8 xl:-mx-10 2xl:-mx-12"><DevLabShell initialProjects={projects} /></div></AppLayout>;
}