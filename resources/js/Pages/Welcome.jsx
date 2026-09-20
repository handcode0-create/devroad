import { Head, Link } from '@inertiajs/react';
import { ArrowRight } from 'lucide-react';

export default function Welcome() {
    return (
        <>
            <Head title="Bienvenue sur DevRoad" />
            <main className="min-h-[100dvh] bg-[#0B0B0B] text-white">
                <div className="mx-auto flex min-h-[100dvh] w-full max-w-[430px] flex-col overflow-hidden bg-[#0B0B0B]">
                    <div className="flex h-[54px] shrink-0 items-center justify-between px-6 pt-2 text-[15px] font-semibold">
                        <span>9:41</span>
                        <span className="text-[11px]">▮▮▮ Wi‑Fi&nbsp;&nbsp;▣ 44</span>
                    </div>

                    <section className="relative h-[49vh] min-h-[330px] max-h-[455px] shrink-0 overflow-hidden px-2">
                        <div className="absolute inset-0">
                            <Tile className="left-[-12%] top-[-8%] h-[58%] w-[66%] -rotate-12" type="laptop" />
                            <Tile className="right-[-8%] top-0 h-[56%] w-[58%] rotate-2" type="notes" />
                            <Tile className="left-[2%] top-[29%] h-[43%] w-[50%] rotate-1" type="phone" />
                            <Tile className="bottom-[1%] left-[5%] h-[29%] w-[42%] -rotate-6" type="audio" />
                            <Tile className="bottom-[2%] right-[3%] h-[30%] w-[40%] rotate-6" type="future" />

                            <div className="absolute left-[39%] top-[39%] z-20 flex h-20 w-20 items-center justify-center rounded-[24px] border border-white/10 bg-[#0B0B0B]/95 shadow-2xl">
                                <img src="/icondevroad.png" alt="DevRoad" className="h-14 w-14 object-contain" />
                            </div>
                        </div>
                        <div className="absolute inset-x-0 bottom-0 z-20 h-36 bg-gradient-to-t from-[#0B0B0B] to-transparent" />
                    </section>

                    <section className="flex flex-1 flex-col px-6 pb-7 pt-1 text-center">
                        <h1 className="text-[clamp(2.8rem,11vw,3.65rem)] font-black leading-[.91] tracking-[-0.065em]">
                            Welcome to
                            <span className="block text-[#FF6A00]">DevRoad</span>
                        </h1>

                        <p className="mx-auto mt-5 max-w-[355px] text-[14px] leading-6 text-white/55">
                            Votre espace tout-en-un pour organiser,
                            planifier et réaliser vos projets plus facilement.
                        </p>

                        <div className="mt-auto pt-7">
                            <Link
                                href={route('login')}
                                className="group flex h-[62px] w-full items-center justify-center gap-5 rounded-full border border-[#FF6A00] bg-[#0D0D0D] text-[16px] font-semibold text-white shadow-[0_0_30px_rgba(255,106,0,.12)]"
                            >
                                <span>Suivant</span>
                                <ArrowRight size={25} />
                            </Link>

                            <div className="mt-6 flex justify-center gap-3">
                                <span className="h-2.5 w-2.5 rounded-full bg-[#FF6A00]" />
                                <span className="h-2.5 w-2.5 rounded-full bg-white/25" />
                                <span className="h-2.5 w-2.5 rounded-full bg-white/25" />
                            </div>
                            <div className="mx-auto mt-5 h-1 w-32 rounded-full bg-white/85" />
                        </div>
                    </section>
                </div>
            </main>
        </>
    );
}

function Tile({ type, className }) {
    const base = "absolute overflow-hidden rounded-[30px] border border-white/[0.08] bg-[#151515] shadow-[0_25px_65px_rgba(0,0,0,.65)] " + className;

    if (type === 'laptop') return (
        <div className={base}>
            <div className="absolute inset-[9%] rotate-[-3deg] rounded-[24px] bg-gradient-to-br from-[#3b3b3b] via-[#171717] to-[#090909] p-5">
                <div className="absolute inset-5 rounded-xl bg-[#090909]" />
                <div className="absolute bottom-5 left-6 right-6 grid grid-cols-7 gap-1">
                    {Array.from({length: 21}).map((_, i) => <span key={i} className="h-2 rounded bg-white/10" />)}
                </div>
            </div>
        </div>
    );

    if (type === 'notes') return (
        <div className={base + " p-5"}>
            <div className="absolute inset-5 rotate-6 rounded-2xl bg-[#181818] p-4">
                <p className="text-sm text-white/70">Ideas</p>
                <p className="mt-2 text-sm text-white/40">Plan</p>
                <p className="text-sm text-white/40">Build</p>
                <p className="text-sm font-bold text-[#FF6A00]">Grow</p>
            </div>
        </div>
    );

    if (type === 'phone') return (
        <div className={base + " flex items-center justify-center"}>
            <div className="relative h-[78%] w-[60%] rounded-[27px] border border-white/10 bg-[#171717]">
                <div className="absolute left-1/2 top-2 h-5 w-16 -translate-x-1/2 rounded-full bg-black" />
                <div className="absolute inset-3 top-10 rounded-[20px] bg-[#0B0B0B]" />
            </div>
        </div>
    );

    if (type === 'audio') return (
        <div className={base + " flex items-center justify-center"}>
            <div className="h-16 w-16 rounded-full border-[10px] border-[#282828] shadow-[0_0_28px_rgba(255,106,0,.18)]">
                <div className="m-2 h-6 w-6 rounded-full bg-[#FF6A00]" />
            </div>
        </div>
    );

    return (
        <div className={base}>
            <div className="absolute inset-5 rounded-2xl bg-gradient-to-br from-[#111] to-[#252525]">
                <p className="absolute bottom-5 left-4 text-[11px] font-semibold leading-4 text-white/35">
                    Better<br />Projects<br /><span className="text-[#FF6A00]">Brighter Future</span>
                </p>
            </div>
        </div>
    );
}
