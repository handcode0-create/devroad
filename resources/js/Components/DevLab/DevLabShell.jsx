import { useEffect, useMemo, useState } from "react";
import { Code2, Copy, FilePlus2, FolderOpen, MoreVertical, Pencil, Plus, Save, Trash2, X } from "lucide-react";
import EditorTabs from "@/Components/DevLab/EditorTabs";
import CodeEditor from "@/Components/DevLab/CodeEditor";
import FileExplorer from "@/Components/DevLab/FileExplorer";
import PreviewPane from "@/Components/DevLab/PreviewPane";

const API="/devlab/projects";

export default function DevLabShell({ initialProjects=[] }) {
 const [projects,setProjects]=useState(initialProjects),[project,setProject]=useState(null),[loading,setLoading]=useState(false),[error,setError]=useState(""),[create,setCreate]=useState(false),[drawer,setDrawer]=useState(false);
 async function request(url,options={}){const r=await fetch(url,{...options,headers:{Accept:"application/json","X-Requested-With":"XMLHttpRequest","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]')?.getAttribute("content")??"",...(options.body?{"Content-Type":"application/json"}:{}),...(options.headers??{})}});const d=r.status===204?{}:await r.json();if(!r.ok)throw Error(d.message||"Une erreur est survenue.");return d}
 useEffect(()=>{void migrateLegacyWorkspaces()},[]);

 async function migrateLegacyWorkspaces(){
  if(typeof window==="undefined")return;
  const prefix="devroad:ide:";
  const keys=Object.keys(window.localStorage).filter(key=>key.startsWith(prefix)&&!key.startsWith("devroad:ide:devlab:"));
  for(const key of keys){
   const markerKey="devroad:devlab:migrated:"+key;
   if(window.localStorage.getItem(markerKey)==="1")continue;
   try{
    const parsed=JSON.parse(window.localStorage.getItem(key)||"null");
    if(!Array.isArray(parsed?.files)||parsed.files.length===0){window.localStorage.setItem(markerKey,"1");continue}
    const files=parsed.files.map(file=>({path:String(file.path??"").trim(),content:String(file.content??"")})).filter(file=>file.path);
    if(!files.length){window.localStorage.setItem(markerKey,"1");continue}
    const stepId=key.slice(prefix.length);
    const d=await request(API+"/import-legacy",{method:"POST",body:JSON.stringify({
      name:"Workspace importé — étape "+stepId,
      template:inferLegacyTemplate(files),
      files
    })});
    setProjects(current=>[d.project,...current]);
    window.localStorage.setItem(markerKey,"1");
   }catch(error){
    console.warn("DevRoad: import legacy DevLab impossible",error);
   }
  }
 }

 async function open(id){setLoading(true);try{setProject((await request(API+"/"+id)).project);setDrawer(false)}catch(e){setError(e.message)}finally{setLoading(false)}}
 async function createProject(p){setLoading(true);try{const d=await request(API,{method:"POST",body:JSON.stringify(p)});setProjects(x=>[d.project,...x]);setProject(d.project);setCreate(false)}catch(e){setError(e.message)}finally{setLoading(false)}}
 async function rename(name){if(!project||!name.trim())return;try{const d=await request(API+"/"+project.id,{method:"PATCH",body:JSON.stringify({name:name.trim()})});setProject(d.project);setProjects(x=>x.map(p=>p.id===d.project.id?{...p,...d.project}:p))}catch(e){setError(e.message)}}
 async function duplicate(){try{const d=await request(API+"/"+project.id+"/duplicate",{method:"POST"});setProjects(x=>[d.project,...x]);setProject(d.project)}catch(e){setError(e.message)}}
 async function remove(){try{await request(API+"/"+project.id,{method:"DELETE"});setProjects(x=>x.filter(p=>p.id!==project.id));setProject(null)}catch(e){setError(e.message)}}
 async function saveFile(file,payload){try{const d=await request(API+"/"+project.id+"/files/"+file.id,{method:"PATCH",body:JSON.stringify(payload)});setProject(p=>({...p,files:p.files.map(f=>f.id===file.id?d.file:f)}))}catch(e){setError(e.message)}}
 async function newFile(){const path=window.prompt("Chemin du fichier","src/app.js");if(!path)return;try{const d=await request(API+"/"+project.id+"/files",{method:"POST",body:JSON.stringify({path,content:""})});setProject(p=>({...p,files:[...p.files,d.file].sort((a,b)=>a.path.localeCompare(b.path))}))}catch(e){setError(e.message)}}
 async function deleteFile(file){if(project.files.length<=1)return;try{await request(API+"/"+project.id+"/files/"+file.id,{method:"DELETE"});setProject(p=>({...p,files:p.files.filter(f=>f.id!==file.id)}))}catch(e){setError(e.message)}}
 return <div className="fixed inset-0 z-50 flex flex-col bg-[#08111F] text-white">
  <header className="flex h-14 shrink-0 items-center gap-2 border-b border-white/[.07] bg-[#0D1725] px-3">
   <button onClick={()=>setDrawer(true)} className="flex min-h-10 items-center gap-2 rounded-xl px-2 hover:bg-white/[.05]" aria-label="Projets"><Code2 size={19} className="text-[#FF6A00]"/><b className="hidden sm:block">Dev<span className="text-[#FF6A00]">Lab</span></b></button>
   {project?<><button onClick={()=>setDrawer(true)} className="max-w-[45vw] truncate rounded-lg px-2 text-sm font-semibold">{project.name}</button><ProjectMenu onRename={rename} onDuplicate={duplicate} onDelete={remove}/></>:<span className="text-xs text-slate-600">{projects.length} projet{projects.length>1?"s":""}</span>}
   <button onClick={()=>setCreate(true)} className="ml-auto inline-flex min-h-10 items-center gap-1.5 rounded-xl bg-[#FF6A00] px-3 text-xs font-bold text-[#08111F]"><Plus size={15}/> Nouveau</button>
  </header>
  {error&&<div className="absolute left-1/2 top-16 z-[70] -translate-x-1/2 rounded-xl border border-red-400/20 bg-red-500/10 px-4 py-2 text-xs text-red-300">{error}<button onClick={()=>setError("")} className="ml-3"><X size={13}/></button></div>}
  {project?<ProjectWorkspace project={project} onSave={saveFile} onNewFile={newFile} onDelete={deleteFile}/>:<ProjectHome projects={projects} loading={loading} onOpen={open} onCreate={()=>setCreate(true)}/>}
  {drawer&&<Drawer projects={projects} onClose={()=>setDrawer(false)} onOpen={open} onCreate={()=>{setDrawer(false);setCreate(true)}}/>}
  {create&&<CreateModal loading={loading} onClose={()=>setCreate(false)} onCreate={createProject}/>}
 </div>;
}

function inferLegacyTemplate(files){
 const paths=files.map(file=>file.path.toLowerCase());
 if(paths.some(path=>path.endsWith(".php"))){
  const laravel=files.some(file=>file.path.toLowerCase()==="routes/web.php"||/illuminate\\support/i.test(file.content));
  return laravel?"laravel":"php";
 }
 return "html";
}

function ProjectMenu({onRename,onDuplicate,onDelete}){const [open,setOpen]=useState(false);return <div className="relative"><button onClick={()=>setOpen(!open)} className="min-h-10 min-w-10 rounded-xl text-slate-500 hover:bg-white/[.05]" aria-label="Actions"><MoreVertical size={17}/></button>{open&&<div className="absolute left-0 top-11 z-50 w-44 rounded-xl border border-white/[.08] bg-[#0D1725] p-1.5 shadow-2xl"><Action icon={Pencil} text="Renommer" onClick={()=>{setOpen(false);const n=window.prompt("Nom du projet");if(n)onRename(n)}}/><Action icon={Copy} text="Dupliquer" onClick={()=>{setOpen(false);onDuplicate()}}/><Action danger icon={Trash2} text="Supprimer" onClick={()=>{setOpen(false);if(window.confirm("Supprimer ce projet ?"))onDelete()}}/></div>}</div>}
function Action({icon:Icon,text,onClick,danger}){return <button onClick={onClick} className={"flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-xs "+(danger?"text-red-400 hover:bg-red-500/10":"text-slate-400 hover:bg-white/[.05] hover:text-white")}><Icon size={13}/>{text}</button>}

function ProjectHome({projects,loading,onOpen,onCreate}){return <main className="min-h-0 flex-1 overflow-auto p-4 sm:p-8"><div className="mx-auto max-w-6xl"><p className="text-[10px] font-bold uppercase tracking-[.15em] text-[#FF8A3D]">Workspace</p><h1 className="mt-1 text-2xl font-extrabold sm:text-3xl">Mes projets</h1><p className="mt-2 max-w-xl text-sm text-slate-500">Un espace indépendant pour coder et organiser tes projets DevLab.</p>{loading?<p className="mt-8 text-sm text-slate-500">Chargement…</p>:projects.length?<div className="mt-8 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">{projects.map(p=><button key={p.id} onClick={()=>onOpen(p.id)} className="rounded-2xl border border-white/[.07] bg-[#0D1725] p-4 text-left hover:border-[#FF6A00]/30"><div className="flex items-center gap-3"><span className="flex h-10 w-10 items-center justify-center rounded-xl bg-[#FF6A00]/10 text-[#FF8A3D]"><FolderOpen size={18}/></span><span className="min-w-0"><b className="block truncate text-sm">{p.name}</b><small className="text-[10px] uppercase text-slate-600">{p.template} · {p.files_count??0} fichiers</small></span></div></button>)}</div>:<button onClick={onCreate} className="mt-8 w-full rounded-2xl border border-dashed border-white/[.1] p-10 text-center text-sm text-slate-500 hover:border-[#FF6A00]/30 hover:text-white"><Plus className="mx-auto text-[#FF8A3D]"/><span className="mt-3 block">Créer ton premier projet</span></button>}</div></main>}

function Drawer({projects,onClose,onOpen,onCreate}){return <div className="absolute inset-0 z-40 bg-black/50" onClick={onClose}><aside onClick={e=>e.stopPropagation()} className="h-full w-[min(360px,88vw)] border-r border-white/[.08] bg-[#0B1523]"><div className="flex h-14 items-center justify-between border-b border-white/[.07] px-4"><b>Projets</b><button onClick={onClose} aria-label="Fermer"><X size={17}/></button></div><div className="p-3"><button onClick={onCreate} className="mb-3 flex w-full items-center gap-2 rounded-xl bg-[#FF6A00] px-3 py-2.5 text-xs font-bold text-[#08111F]"><Plus size={14}/> Nouveau projet</button>{projects.map(p=><button key={p.id} onClick={()=>onOpen(p.id)} className="mb-1 flex w-full items-center gap-3 rounded-xl px-3 py-3 text-left hover:bg-white/[.04]"><FolderOpen size={15} className="text-[#FF8A3D]"/><span className="truncate text-xs">{p.name}</span></button>)}</div></aside></div>}

function CreateModal({loading,onClose,onCreate}){const [name,setName]=useState("Mon projet"),[template,setTemplate]=useState("html");return <div className="absolute inset-0 z-[60] flex items-center justify-center bg-black/60 p-4" role="dialog" aria-modal="true"><div className="w-full max-w-md rounded-2xl border border-white/[.08] bg-[#0D1725] p-5"><div className="flex justify-between"><b>Nouveau projet</b><button onClick={onClose} aria-label="Fermer"><X size={16}/></button></div><label className="mt-5 block text-xs text-slate-400">Nom<input value={name} onChange={e=>setName(e.target.value)} className="mt-2 w-full rounded-xl border border-white/[.08] bg-[#08111F] p-3 text-sm outline-none focus:border-[#FF6A00]/50"/></label><label className="mt-4 block text-xs text-slate-400">Template<select value={template} onChange={e=>setTemplate(e.target.value)} className="mt-2 w-full rounded-xl border border-white/[.08] bg-[#08111F] p-3 text-sm outline-none"><option value="html">HTML / CSS / JavaScript</option><option value="node">Node.js</option><option value="php">PHP</option><option value="laravel">Laravel</option></select></label><div className="mt-5 flex justify-end gap-2"><button onClick={onClose} className="px-4 py-2 text-xs text-slate-500">Annuler</button><button disabled={loading||!name.trim()} onClick={()=>onCreate({name,template})} className="rounded-xl bg-[#FF6A00] px-4 py-2.5 text-xs font-bold text-[#08111F] disabled:opacity-40">Créer</button></div></div></div>}

function ProjectWorkspace({project,onSave,onNewFile,onDelete}){const files=project.files??[],[active,setActive]=useState(files[0]?.path??""),[draft,setDraft]=useState(files[0]?.content??""),[saved,setSaved]=useState(true),[panel,setPanel]=useState("editor");const current=files.find(f=>f.path===active)||files[0];useEffect(()=>{setActive(files[0]?.path??"")},[project.id]);useEffect(()=>{const f=files.find(x=>x.path===active)||files[0];setDraft(f?.content??"");setSaved(true)},[active,project.id]);useEffect(()=>{if(saved||!current)return;const timer=window.setTimeout(async()=>{try{await onSave(current,{path:current.path,content:draft});setSaved(true)}catch{}},900);return()=>window.clearTimeout(timer)},[draft,saved,current?.id,project.id]);const srcDoc=useMemo(()=>{const html=files.find(f=>f.path.toLowerCase()==="index.html")?.content||(current?.path.endsWith(".html")?draft:"");const css=files.find(f=>f.path.toLowerCase().endsWith(".css"))?.content||"";const js=files.find(f=>f.path.toLowerCase().endsWith(".js"))?.content||"";return html?html.replace("</head>","<style>"+css+"</style></head>").replace("</body>","<script>"+js.replaceAll("</script>","")+"</script></body>"):"<!doctype html><body><pre>Aucun aperçu HTML.</pre></body>"},[files,current,draft]);async function save(){if(current&&!saved){try{await onSave(current,{path:current.path,content:draft});setSaved(true)}catch{}}}return <div className="min-h-0 flex-1 overflow-hidden"><div className="grid h-full min-h-0 lg:grid-cols-[220px_minmax(0,1fr)_minmax(280px,34vw)]"><FileExplorer files={files} activeFile={active} onSelect={setActive} onCreate={onNewFile} onImport={()=>{}}/><section className="min-w-0 overflow-hidden bg-[#06101A]"><div className="border-b border-white/[.06] bg-[#0D1725] p-2"><EditorTabs files={files} activeFile={active} onSelect={setActive} onCreate={onNewFile} onImport={()=>{}}/></div><CodeEditor activeFile={active} currentFile={{...current,content:draft}} lineCount={Math.max(1,draft.split("\n").length)} copied={false} onChange={v=>{setDraft(v);setSaved(false)}} onCopy={()=>navigator.clipboard?.writeText(draft)} onDelete={()=>current&&onDelete(current)}/><div className="flex h-12 items-center justify-between border-t border-white/[.06] bg-[#0D1725] px-3"><span className="text-[10px] text-slate-600">{saved?"Enregistré":"Modifications non enregistrées"}</span><button onClick={save} disabled={saved} className="inline-flex items-center gap-1.5 rounded-lg bg-[#FF6A00] px-3 py-2 text-[10px] font-bold text-[#08111F] disabled:opacity-30"><Save size={13}/> Enregistrer</button></div></section><aside className="hidden min-w-0 border-l border-white/[.06] bg-[#050B12] lg:block"><PreviewPane previewVersion={0} srcDoc={srcDoc} onRefresh={()=>{}}/></aside></div><div className="flex border-t border-white/[.07] bg-[#0D1725] p-2 lg:hidden"><button onClick={()=>setPanel("editor")} className={"flex-1 rounded-lg py-2 text-xs font-semibold "+(panel==="editor"?"bg-[#FF6A00] text-[#08111F]":"text-slate-500")}>Éditeur</button><button onClick={()=>setPanel("preview")} className={"flex-1 rounded-lg py-2 text-xs font-semibold "+(panel==="preview"?"bg-[#FF6A00] text-[#08111F]":"text-slate-500")}>Aperçu</button></div>{panel==="preview"&&<div className="absolute inset-x-0 bottom-0 z-30 bg-[#050B12] p-2 lg:hidden"><PreviewPane previewVersion={0} srcDoc={srcDoc} onRefresh={()=>{}}/></div>}</div>}
