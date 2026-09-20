import { Head, Link } from '@inertiajs/react';
import {
    ArrowRight,
    BarChart3,
    CheckSquare,
    Code2,
    FolderKanban,
    Lightbulb,
    Target,
    Users,
} from 'lucide-react';

export default function Welcome() {
    return (
        <>
            <Head title="Bienvenue" />

            <div className="min-h-[100dvh] overflow-hidden bg-[#F8F9FC] text-[#0B0D12]">
                <div className="pointer-events-none fixed inset-0 overflow-hidden">
                    <div className="absolute -left-[18%] top-[-28%] h-[75vh] w-[55vw] rounded-full bg-white blur-3xl" />
                    <div className="absolute right-[-20%] top-[-10%] h-[90vh] w-[55vw] rounded-full bg-[#D9DAF7]/60 blur-3xl" />
                    <div className="absolute bottom-[-35%] right-[-10%] h-[70vh] w-[65vw] rounded-full bg-[#C9C9F4]/60 blur-3xl" />
                    <div className="absolute inset-0 bg-[linear-gradient(115deg,rgba(255,255,255,.9)_0%,rgba(255,255,255,.55)_42%,rgba(225,226,249,.72)_100%)]" />
                    <div className="absolute inset-y-0 right-0 w-[42%] bg-[repeating-linear-gradient(103deg,transparent_0,transparent_90px,rgba(255,255,255,.16)_92px,transparent_180px)] opacity-70" />
                </div>

                <main className="relative mx-auto flex min-h-[100dvh] w-full max-w-[1720px] flex-col px-5 py-6 sm:px-8 sm:py-8 lg:px-12 lg:py-9 xl:px-16">
                    <header className="flex items-center justify-between">
                        <Link href="/" className="flex items-center gap-3">
                            <img
                                src="/icondevroad.png"
                                alt="DevRoad"
                                className="h-10 w-10 object-contain sm:h-11 sm:w-11"
                            />
                            <span className="text-xl font-extrabold tracking-tight sm:text-2xl">
                                DevRoad
                            </span>
                        </Link>

                        <div className="flex items-center gap-2">
                            <span className="hidden rounded-full border border-white/70 bg-white/50 px-4 py-2 text-sm font-semibold shadow-sm backdrop-blur-xl sm:inline-flex">
                                La route vers vos projets
                            </span>
                            <span className="inline-flex rounded-full border border-white/70 bg-white/55 px-4 py-2 text-sm font-semibold shadow-sm backdrop-blur-xl">
                                2025
                            </span>
                        </div>
                    </header>

                    <section className="grid flex-1 items-center gap-12 pb-3 pt-12 lg:grid-cols-[minmax(390px,0.78fr)_minmax(600px,1.22fr)] lg:gap-8 lg:pt-8 xl:gap-14">
                        <div className="z-10 max-w-[590px]">
                            <div className="mb-7 inline-flex items-center gap-2 rounded-full border border-white/80 bg-white/60 px-4 py-2 text-sm font-medium shadow-[0_8px_30px_rgba(80,85,130,.06)] backdrop-blur-xl">
                                <span className="h-2.5 w-2.5 rounded-full bg-[#FF6A00]" />
                                Bienvenue
                            </div>

                            <h1 className="text-[clamp(3.5rem,7vw,6.8rem)] font-black leading-[0.91] tracking-[-0.055em]">
                                Welcome to
                                <span className="block text-[#FF6A00]">DevRoad</span>
                            </h1>

                            <p className="mt-7 max-w-[540px] text-lg leading-7 text-[#5B6070] sm:text-xl sm:leading-8">
                                Votre espace tout-en-un pour organiser,
                                planifier et réaliser vos projets plus
                                facilement. Des idées claires, une meilleure
                                productivité et un avenir plus structuré.
                            </p>

                            <div className="mt-8 grid max-w-[520px] grid-cols-2 gap-x-7 gap-y-5">
                                <Feature icon={CheckSquare} text={<>Organisez<br />vos projets</>} tone="orange" />
                                <Feature icon={Users} text={<>Collaborez<br />en équipe</>} tone="green" />
                                <Feature icon={Lightbulb} text={<>Boostez<br />votre productivité</>} tone="purple" />
                                <Feature icon={Target} text={<>Atteignez<br />vos objectifs</>} tone="pink" />
                            </div>

                            <Link
                                href={route('login')}
                                className="group mt-10 flex h-[62px] w-full max-w-[440px] items-center justify-center gap-5 rounded-full bg-[#171717] px-7 text-base font-semibold text-white shadow-[0_16px_35px_rgba(0,0,0,.18)] transition hover:-translate-y-0.5 hover:bg-black sm:h-[68px] sm:text-lg"
                            >
                                <span>Suivant</span>
                                <ArrowRight size={22} className="transition-transform group-hover:translate-x-1" />
                            </Link>

                            <div className="mt-7 flex items-center gap-2 px-1">
                                <span className="h-2.5 w-5 rounded-full bg-[#FF6A00]" />
                                <span className="h-2.5 w-2.5 rounded-full bg-[#E2E3E9]" />
                                <span className="h-2.5 w-2.5 rounded-full bg-[#E2E3E9]" />
                            </div>
                        </div>

                        <div className="relative mx-auto flex min-h-[520px] w-full max-w-[830px] items-center justify-center lg:min-h-[650px]">
                            <div className="absolute right-[-15%] top-[5%] h-[88%] w-[75%] rounded-[70px] bg-white/25 blur-2xl" />

                            <div className="relative w-full rotate-[-2deg] rounded-[34px] border border-white/80 bg-[#EDEEF4]/85 p-3 shadow-[0_40px_100px_rgba(67,70,120,.20)] backdrop-blur-xl sm:rounded-[46px] sm:p-4">
                                <div className="overflow-hidden rounded-[25px] border border-white/90 bg-[#F8F8FA] shadow-inner sm:rounded-[35px]">
                                    <div className="flex h-9 items-center gap-1.5 border-b border-black/[0.06] bg-white/80 px-4 sm:h-11 sm:px-5">
                                        <span className="h-2.5 w-2.5 rounded-full bg-[#E5E5E8]" />
                                        <span className="h-2.5 w-2.5 rounded-full bg-[#E5E5E8]" />
                                        <span className="h-2.5 w-2.5 rounded-full bg-[#E5E5E8]" />
                                        <div className="mx-auto h-5 w-[42%] rounded-full bg-[#F0F0F3]" />
                                    </div>

                                    <div className="grid min-h-[430px] grid-cols-[82px_1fr] bg-[#F7F7F9] sm:min-h-[550px] sm:grid-cols-[150px_1fr]">
                                        <aside className="border-r border-black/[0.05] bg-white/70 p-3 sm:p-5">
                                            <div className="flex items-center gap-2">
                                                <img src="/icondevroad.png" alt="" className="h-7 w-7 object-contain sm:h-8 sm:w-8" />
                                                <span className="hidden text-sm font-bold sm:block">DevRoad</span>
                                            </div>
                                            <div className="mt-8 space-y-3">
                                                <MiniNav active icon={FolderKanban} label="Projets" />
                                                <MiniNav icon={CheckSquare} label="Tâches" />
                                                <MiniNav icon={BarChart3} label="Stats" />
                                            </div>
                                        </aside>

                                        <div className="p-4 sm:p-7">
                                            <div className="flex items-start justify-between gap-4">
                                                <div>
                                                    <p className="text-[10px] font-semibold uppercase tracking-[0.14em] text-[#9699A5]">Tableau de bord</p>
                                                    <h2 className="mt-1 text-2xl font-black tracking-tight sm:text-4xl">Plan développez<br />réussissez.</h2>
                                                    <p className="mt-2 max-w-[330px] text-xs leading-5 text-[#858997] sm:text-sm">Des outils simples pour transformer vos idées en projets concrets.</p>
                                                </div>
                                                <div className="hidden h-11 w-11 items-center justify-center rounded-2xl bg-[#FF6A00] text-white shadow-lg shadow-orange-500/20 sm:flex">
                                                    <Code2 size={21} />
                                                </div>
                                            </div>

                                            <div className="mt-6 grid grid-cols-2 gap-3 sm:mt-9 sm:grid-cols-3">
                                                <Metric label="Projets" value="12" detail="+3 ce mois" />
                                                <Metric label="Tâches" value="28" detail="+12 terminées" />
                                                <Metric label="Équipe" value="5" detail="En collaboration" />
                                            </div>

                                            <div className="mt-4 rounded-[24px] border border-black/[0.05] bg-white p-4 shadow-sm sm:p-5">
                                                <div className="flex items-center justify-between">
                                                    <span className="text-xs font-bold">Progression globale</span>
                                                    <span className="text-xs font-bold text-[#FF6A00]">72%</span>
                                                </div>
                                                <div className="mt-3 h-2 overflow-hidden rounded-full bg-[#EEF0F3]">
                                                    <div className="h-full w-[72%] rounded-full bg-[#FF6A00]" />
                                                </div>
                                                <div className="mt-5 grid grid-cols-3 gap-2">
                                                    <div className="h-16 rounded-2xl bg-[#F5F6F8]" />
                                                    <div className="h-16 rounded-2xl bg-[#F5F6F8]" />
                                                    <div className="h-16 rounded-2xl bg-[#F5F6F8]" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div className="absolute -bottom-1 left-[2%] w-[43%] rotate-[3deg] rounded-[28px] border border-white/80 bg-white/80 p-5 shadow-[0_25px_60px_rgba(75,77,120,.16)] backdrop-blur-xl sm:p-6">
                                <div className="flex items-start justify-between">
                                    <div>
                                        <p className="text-[10px] font-bold uppercase tracking-widest text-[#A1A3AE]">Idées</p>
                                        <p className="mt-2 font-serif text-xl italic leading-tight text-[#30323A] sm:text-2xl">Big Ideas<br />Better Projects</p>
                                    </div>
                                    <div className="h-9 w-9 rounded-full bg-[#FF6A00]/10" />
                                </div>
                            </div>

                            <div className="absolute -right-[1%] bottom-[5%] w-[30%] rotate-[4deg] rounded-[25px] border border-white/80 bg-white/75 p-4 shadow-[0_25px_60px_rgba(75,77,120,.14)] backdrop-blur-xl sm:p-5">
                                <p className="text-[10px] font-semibold text-[#9295A2]">Objectif</p>
                                <p className="mt-2 text-sm font-bold leading-5 sm:text-base">Les petites avancées construisent les grandes compétences.</p>
                                <div className="mt-4 h-1 w-12 rounded-full bg-[#FF6A00]" />
                            </div>
                        </div>
                    </section>

                    <footer className="flex justify-end pb-1 pt-5">
                        <div className="rounded-full bg-[#171717] px-6 py-3 text-sm font-semibold text-white shadow-[0_12px_30px_rgba(0,0,0,.16)]">
                            hello@devroad.app
                        </div>
                    </footer>
                </main>
            </div>
        </>
    );
}

function Feature({ icon: Icon, text, tone }) {
    const tones = {
        orange: 'bg-orange-50 text-[#FF6A00]',
        green: 'bg-emerald-50 text-emerald-600',
        purple: 'bg-violet-50 text-violet-600',
        pink: 'bg-rose-50 text-rose-500',
    };

    return (
        <div className="flex items-center gap-3">
            <span className={`flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl ${tones[tone]}`}>
                <Icon size={22} strokeWidth={2.1} />
            </span>
            <span className="text-sm font-medium leading-5 text-[#2D3038] sm:text-base">{text}</span>
        </div>
    );
}

function MiniNav({ icon: Icon, label, active }) {
    return (
        <div className={`flex items-center gap-2 rounded-xl px-2 py-2 ${active ? 'bg-orange-50 text-[#FF6A00]' : 'text-[#9B9DA7]'}`}>
            <Icon size={15} />
            <span className="hidden text-[10px] font-semibold sm:block">{label}</span>
        </div>
    );
}

function Metric({ label, value, detail }) {
    return (
        <div className="rounded-[20px] border border-black/[0.05] bg-white p-3 shadow-sm sm:p-4">
            <p className="text-[9px] font-semibold text-[#9A9CA8] sm:text-[10px]">{label}</p>
            <div className="mt-1 flex items-end justify-between gap-1">
                <p className="text-xl font-black tracking-tight sm:text-2xl">{value}</p>
                <BarChart3 size={15} className="text-[#FF6A00]" />
            </div>
            <p className="mt-1 text-[8px] text-[#9A9CA8] sm:text-[9px]">{detail}</p>
        </div>
    );
}
