import axios from "axios";
import { useEffect, useRef, useState } from "react";
import useGsapScrollReveal from "@/hooks/useGsapScrollReveal";
import { ArrowLeft, Code2, Copy, FolderOpen, MoreVertical, Pencil, Plus, Trash2, X } from "lucide-react";
import DevLabWorkspace from "@/Components/DevLab/DevLabWorkspace";

const API="/devlab/projects";

function requestError(error) {
 const response=error?.response;
 const validation=Object.values(response?.data?.errors??{}).flat();
 return validation[0]??response?.data?.message??error?.message??"Une erreur est survenue.";
}

export default function DevLabShell({ initialProjects=[], initialProjectId=null }) {
 const [projects,setProjects]=useState(initialProjects),[project,setProject]=useState(null),[loading,setLoading]=useState(false),[error,setError]=useState(""),[create,setCreate]=useState(false),[drawer,setDrawer]=useState(false);
 async function request(url,options={}){setError("");const method=(options.method||"GET").toLowerCase();const headers={Accept:"application/json","X-Requested-With":"XMLHttpRequest",...(options.headers??{})};if(["post","put","patch","delete"].includes(method)){const csrf=await axios.get("/devlab/projects/csrf-token",{headers:{Accept:"application/json"},withCredentials:true,withXSRFToken:true});headers["X-CSRF-TOKEN"]=csrf.data.token;}const response=await axios.request({url,method,data:options.body?JSON.parse(options.body):undefined,headers,withCredentials:true,withXSRFToken:true});return response.data}
 useEffect(()=>{void migrateLegacyWorkspaces()},[]);
 useEffect(()=>{if(initialProjectId){void open(initialProjectId)}},[initialProjectId]);

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

 async function open(id){setLoading(true);try{setProject((await request(API+"/"+id)).project);setDrawer(false)}catch(e){setError(requestError(e))}finally{setLoading(false)}}
 async function createProject(p){setLoading(true);try{const d=await request(API,{method:"POST",body:JSON.stringify(p)});setProjects(x=>[d.project,...x]);setProject(d.project);setCreate(false)}catch(e){setError(requestError(e))}finally{setLoading(false)}}
 async function rename(name){if(!project||!name.trim())return;try{const d=await request(API+"/"+project.id,{method:"PATCH",body:JSON.stringify({name:name.trim()})});setProject(d.project);setProjects(x=>x.map(p=>p.id===d.project.id?{...p,...d.project}:p))}catch(e){setError(requestError(e))}}
 async function duplicate(){try{const d=await request(API+"/"+project.id+"/duplicate",{method:"POST"});setProjects(x=>[d.project,...x]);setProject(d.project)}catch(e){setError(requestError(e))}}
 async function remove(){try{await request(API+"/"+project.id,{method:"DELETE"});setProjects(x=>x.filter(p=>p.id!==project.id));setProject(null)}catch(e){setError(requestError(e))}}
 async function saveFile(file,payload){try{const d=await request(API+"/"+project.id+"/files/"+file.id,{method:"PATCH",body:JSON.stringify(payload)});setProject(p=>({...p,files:p.files.map(f=>f.id===file.id?d.file:f)}))}catch(e){setError(requestError(e));throw e}}
 async function newFile(){const path=window.prompt("Chemin du fichier","src/app.js");if(!path)return;try{const d=await request(API+"/"+project.id+"/files",{method:"POST",body:JSON.stringify({path,content:""})});setProject(p=>({...p,files:[...p.files,d.file].sort((a,b)=>a.path.localeCompare(b.path))}));return d.file}catch(e){setError(requestError(e));return null}}
 async function renameFile(file){const path=window.prompt("Nouveau nom / chemin du fichier",file.path);if(!path||path.trim()===file.path)return null;try{const d=await request(API+"/"+project.id+"/files/"+file.id,{method:"PATCH",body:JSON.stringify({path:path.trim(),content:file.content??""})});setProject(p=>({...p,files:p.files.map(f=>f.id===file.id?d.file:f).sort((a,b)=>a.path.localeCompare(b.path))}));return d.file}catch(e){setError(requestError(e));return null}}
 async function duplicateFile(file){const lastSlash=file.path.lastIndexOf("/");const dir=lastSlash>=0?file.path.slice(0,lastSlash+1):"";const name=lastSlash>=0?file.path.slice(lastSlash+1):file.path;const dot=name.lastIndexOf(".");const base=dot>0?name.slice(0,dot):name;const ext=dot>0?name.slice(dot):"";let path=dir+base+"-copy"+ext;let index=2;while(project.files.some(f=>f.path===path)){path=dir+base+"-copy-"+index+ext;index+=1}try{const d=await request(API+"/"+project.id+"/files",{method:"POST",body:JSON.stringify({path,content:file.content??""})});setProject(p=>({...p,files:[...p.files,d.file].sort((a,b)=>a.path.localeCompare(b.path))}));return d.file}catch(e){setError(requestError(e));return null}}
 async function deleteFile(file){
  if(project.files.length<=1){setError("Un projet doit conserver au moins un fichier.");return false}
  if(!window.confirm("Supprimer le fichier « "+file.path+" » ?")) return false;
  try{
   const deleted=await request(API+"/"+project.id+"/files/"+file.id,{method:"DELETE"});
   setProject(p=>({...p,files:p.files.filter(f=>f.id!==file.id)}));
   return true;
  }catch(e){setError(requestError(e));return false}
 }
 return <div className="fixed inset-0 z-[60] flex flex-col bg-[#08111F] text-white">
  <header className="flex h-14 shrink-0 items-center gap-2 border-b border-white/[.07] bg-[#0D1725] px-3">
   <button type="button" onClick={()=>{if(window.history.length>1){window.history.back()}else{window.location.href="/dashboard"}}} className="inline-flex min-h-10 min-w-10 items-center justify-center gap-1.5 rounded-xl px-2 text-slate-400 hover:bg-white/[.05] hover:text-white" aria-label="Retour à la page précédente" title="Retour">
    <ArrowLeft size={18}/><span className="hidden text-xs font-semibold sm:inline">Retour</span>
   </button>
   <button onClick={()=>setDrawer(true)} className="flex min-h-10 items-center gap-2 rounded-xl px-2 hover:bg-white/[.05]" aria-label="Projets"><Code2 size={19} className="text-[#FF6A00]"/><b className="hidden sm:block">Dev<span className="text-[#FF6A00]">Lab</span></b></button>
   {project?<><button onClick={()=>setDrawer(true)} className="max-w-[45vw] truncate rounded-lg px-2 text-sm font-semibold">{project.name}</button><ProjectMenu onRename={rename} onDuplicate={duplicate} onDelete={remove}/></>:<span className="text-xs text-slate-600">{projects.length} projet{projects.length>1?"s":""}</span>}
   <button onClick={()=>setCreate(true)} className="ml-auto inline-flex min-h-10 items-center gap-1.5 rounded-xl bg-[#FF6A00] px-3 text-xs font-bold text-[#08111F]"><Plus size={15}/> Nouveau</button>
  </header>
  {error&&<div className="absolute left-1/2 top-16 z-[70] -translate-x-1/2 rounded-xl border border-red-400/20 bg-red-500/10 px-4 py-2 text-xs text-red-300">{error}<button onClick={()=>setError("")} className="ml-3"><X size={13}/></button></div>}
  {project?<DevLabWorkspace
    project={project}
    onSave={saveFile}
    onNewFile={newFile}
    onRename={renameFile}
    onDuplicate={duplicateFile}
    onDelete={deleteFile}
    onImportFiles={async (files) => {
      for (const file of files) {
        const existing = project.files?.find((item) => item.path === file.path);
        if (existing) {
          await saveFile(existing, { path: existing.path, content: file.content });
        } else {
          const response = await request(API + "/" + project.id + "/files", {
            method: "POST",
            body: JSON.stringify(file),
          });
          setProject((current) => ({
            ...current,
            files: [...current.files, response.file].sort((a, b) => a.path.localeCompare(b.path)),
          }));
        }
      }
    }}
  />:<ProjectHome projects={projects} loading={loading} onOpen={open} onCreate={()=>setCreate(true)}/>}
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

function ProjectHome({projects,loading,onOpen,onCreate}) {
 const animationRef=useRef(null);
 useGsapScrollReveal(animationRef,[projects.length,loading]);

 return <main ref={animationRef} className="min-h-0 flex-1 overflow-auto p-4 sm:p-8">
  <div className="mx-auto max-w-6xl">
   <p data-gsap-reveal className="text-[10px] font-bold uppercase tracking-[.15em] text-[#FF8A3D]">Workspace</p>
   <h1 data-gsap-reveal className="mt-1 text-2xl font-extrabold sm:text-3xl">Mes projets</h1>
   <p data-gsap-reveal className="mt-2 max-w-xl text-sm text-slate-500">Un espace indépendant pour coder et organiser tes projets DevLab.</p>
   {loading?<p data-gsap-reveal className="mt-8 text-sm text-slate-500">Chargement…</p>:projects.length?
    <div data-gsap-reveal className="mt-8 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
     {projects.map(p=><button data-gsap-reveal key={p.id} onClick={()=>onOpen(p.id)} className="rounded-2xl border border-white/[.07] bg-[#0D1725] p-4 text-left transition duration-200 hover:-translate-y-0.5 hover:border-[#FF6A00]/30"><div className="flex items-center gap-3"><span className="flex h-10 w-10 items-center justify-center rounded-xl bg-[#FF6A00]/10 text-[#FF8A3D]"><FolderOpen size={18}/></span><span className="min-w-0"><b className="block truncate text-sm">{p.name}</b><small className="text-[10px] uppercase text-slate-600">{p.template} · {p.files_count??0} fichiers</small></span></div></button>)}
    </div>
    :<button data-gsap-reveal onClick={onCreate} className="mt-8 w-full rounded-2xl border border-dashed border-white/[.1] p-10 text-center text-sm text-slate-500 transition hover:border-[#FF6A00]/30 hover:text-white"><Plus className="mx-auto text-[#FF8A3D]"/><span className="mt-3 block">Créer ton premier projet</span></button>}
  </div>
 </main>;
}

function Drawer({projects,onClose,onOpen,onCreate}){return <div className="absolute inset-0 z-40 bg-black/50" onClick={onClose}><aside onClick={e=>e.stopPropagation()} className="h-full w-[min(360px,88vw)] border-r border-white/[.08] bg-[#0B1523]"><div className="flex h-14 items-center justify-between border-b border-white/[.07] px-4"><b>Projets</b><button onClick={onClose} aria-label="Fermer"><X size={17}/></button></div><div className="p-3"><button onClick={onCreate} className="mb-3 flex w-full items-center gap-2 rounded-xl bg-[#FF6A00] px-3 py-2.5 text-xs font-bold text-[#08111F]"><Plus size={14}/> Nouveau projet</button>{projects.map(p=><button key={p.id} onClick={()=>onOpen(p.id)} className="mb-1 flex w-full items-center gap-3 rounded-xl px-3 py-3 text-left hover:bg-white/[.04]"><FolderOpen size={15} className="text-[#FF8A3D]"/><span className="truncate text-xs">{p.name}</span></button>)}</div></aside></div>}

function CreateModal({loading,onClose,onCreate}){const [name,setName]=useState("Mon projet"),[template,setTemplate]=useState("html");return <div className="absolute inset-0 z-[60] flex items-center justify-center bg-black/60 p-4" role="dialog" aria-modal="true"><div className="w-full max-w-md rounded-2xl border border-white/[.08] bg-[#0D1725] p-5"><div className="flex justify-between"><b>Nouveau projet</b><button onClick={onClose} aria-label="Fermer"><X size={16}/></button></div><label className="mt-5 block text-xs text-slate-400">Nom<input value={name} onChange={e=>setName(e.target.value)} className="mt-2 w-full rounded-xl border border-white/[.08] bg-[#08111F] p-3 text-sm outline-none focus:border-[#FF6A00]/50"/></label><label className="mt-4 block text-xs text-slate-400">Template<select value={template} onChange={e=>setTemplate(e.target.value)} className="mt-2 w-full rounded-xl border border-white/[.08] bg-[#08111F] p-3 text-sm outline-none"><option value="html">HTML / CSS / JavaScript</option><option value="node">Node.js</option><option value="php">PHP</option><option value="laravel">Laravel</option></select></label><div className="mt-5 flex justify-end gap-2"><button onClick={onClose} className="px-4 py-2 text-xs text-slate-500">Annuler</button><button disabled={loading||!name.trim()} onClick={()=>onCreate({name,template})} className="rounded-xl bg-[#FF6A00] px-4 py-2.5 text-xs font-bold text-[#08111F] disabled:opacity-40">Créer</button></div></div></div>}

