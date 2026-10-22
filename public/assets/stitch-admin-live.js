/* Existing Stitch order rendering is reused with real API data. */
(() => {
 const L=window.StitchLive,C=window.StitchConfig,$=s=>document.querySelector(s),$$=s=>[...document.querySelectorAll(s)],id=s=>document.getElementById(s);
 const escape=v=>String(v??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
 const statusChoices=current=>[...new Set([current,'Đang xử lý','Chờ xử lý','Đang giao','Đã hoàn thành','Đã hủy'])];
 if(C.page==='orders'){
  ordersData=[];customersData=[];
  const sourceCustomerRenderer=window.renderCustomersTable,sourceEditCustomer=window.editCustomer;
  const decode=v=>{const e=document.createElement('textarea');e.innerHTML=v||'';return e.value};
  // Keep Stitch's row markup while binding names safely outside inline JavaScript.
  window.renderCustomersTable=function(data){sourceCustomerRenderer(data);for(const [i,row] of [...id('customers-table-body').children].entries()){const u=data[i],buttons=row.querySelectorAll('button');L.button(buttons[0],()=>window.editCustomer(u.id));L.button(buttons[1],()=>promptDeleteCustomer(u.id,decode(u.fullname)));}};
  window.editCustomer=function(n){sourceEditCustomer(n);const u=customersData.find(x=>x.id===n);if(u)for(const k of ['username','fullname','phone','address','email'])L.val('cust-field-'+k,decode(u[k]));};
  async function load(){const [os,us]=await Promise.all([L.api('/orders'),L.api('/users')]);customersData=us.map(u=>({...u,id:String(u.id),fullname:escape(u.fullname),username:escape(u.username),phone:escape(u.phone),address:escape(u.address),email:escape(u.email)}));ordersData=os.map(o=>({id:String(o.id),username:escape(o.username),customerName:escape(o.fullname||o.username),phone:escape(us.find(u=>u.username===o.username)?.phone||''),date:escape(o.created_at),rawAmount:Number(o.total_price),status:o.status,notes:escape(o.notes),paymentMethod:o.payment_method,items:o.items.map(b=>({title:escape(b.title),author:'',cover:b.image?C.base+'/images/'+encodeURIComponent(b.image):C.base+'/public/assets/stitch-empty-cover.svg',coverAlt:escape(b.title),price:Number(b.price),quantity:Number(b.quantity)}))}));applyOrderFilters();filterCustomers();updateOrderSummaryStats(ordersData);}
  const sourceOrderRenderer=window.renderOrdersTable,sourceOpenOrder=window.openOrderDetail,updating=new Set();
  const detailSelect=id('detail-status-select'),detailSave=id('detail-status-save'),detailStatus=document.createElement('p');detailStatus.id='order-status-feedback';detailStatus.className='stitch-form-status';detailStatus.setAttribute('role','status');detailSelect?.parentElement?.parentElement?.after(detailStatus);
  function detailControls(){const order=ordersData.find(o=>String(o.id)===String(activeDetailOrderId));if(!order)return;const choices=statusChoices(order.status);for(const option of detailSelect.options)option.disabled=!choices.includes(option.value);detailSelect.disabled=choices.length===1||updating.has(String(order.id));detailSave.disabled=updating.has(String(order.id))||detailSelect.value===order.status;detailSave.setAttribute('aria-busy',String(updating.has(String(order.id))));detailSelect.title=choices.length===1?'Đơn đã kết thúc, không thể chuyển trạng thái.':'Chọn trạng thái tiếp theo rồi bấm Lưu trạng thái.';}
  detailSelect?.addEventListener('change',detailControls);
  window.renderOrdersTable=function(data){sourceOrderRenderer(data);for(const [index,row] of [...id('orders-table-body').children].entries()){const order=data[index],trigger=row.querySelector('button[title="Đổi trạng thái nhanh"]');if(!order||!trigger)continue;const select=document.createElement('select');select.className='stitch-order-status-select';select.setAttribute('aria-label','Đổi trạng thái đơn #'+order.id);select.dataset.orderId=order.id;const choices=statusChoices(order.status);select.replaceChildren(...choices.map(s=>new Option(s,s)));select.value=order.status;select.disabled=choices.length===1||updating.has(String(order.id));select.title=choices.length===1?'Đơn đã kết thúc.':'Chọn trạng thái để cập nhật';select.addEventListener('change',()=>window.quickChangeStatus(order.id,select.value));trigger.parentElement.replaceWith(select);}};
  window.openOrderDetail=function(n){sourceOpenOrder(String(n));let metadata=id('detail-checkout-metadata');if(!metadata){metadata=document.createElement('p');metadata.id='detail-checkout-metadata';metadata.style.whiteSpace='pre-wrap';detailStatus.after(metadata)}const order=ordersData.find(o=>String(o.id)===String(n));if(order)L.text(metadata,'Thanh toán: '+(order.paymentMethod==='qr'?'QR DEMO — chưa xác nhận thanh toán':'Tiền mặt khi nhận hàng')+'\nGhi chú: '+(decode(order.notes)||'Không có'));L.text(detailStatus,'');detailControls();};
  async function updateStatus(n,next){const key=String(n),order=ordersData.find(o=>String(o.id)===key);if(!order||updating.has(key)||order.status===next)return;if(!statusChoices(order.status).includes(next)){L.notice('Trạng thái này không phù hợp với trạng thái hiện tại của đơn.');return}updating.add(key);for(const select of $$('select[data-order-id]'))if(select.dataset.orderId===key)select.disabled=true;detailControls();L.text(detailStatus,'Đang lưu trạng thái…');let saved=false;try{await L.api('/orders/'+Number(n),'PUT',{status:next});saved=true;await load();if(String(activeDetailOrderId)===key)window.openOrderDetail(key);detailStatus.dataset.success='true';L.text(detailStatus,'Đã lưu trạng thái: '+next);L.notice('Đã cập nhật đơn #'+n+' → '+next);}catch(error){if(saved)order.status=next;applyOrderFilters();detailStatus.dataset.success='false';const message=saved?'Đã lưu trạng thái nhưng chưa tải lại được danh sách. Hãy tải lại trang.':error.message;L.text(detailStatus,message);L.notice(message);if(String(activeDetailOrderId)===key&&!saved)detailSelect.value=order.status;}finally{updating.delete(key);for(const select of $$('select[data-order-id]'))if(select.dataset.orderId===key){const current=ordersData.find(o=>String(o.id)===key);select.value=current?.status||order.status;select.disabled=statusChoices(select.value).length===1}detailControls();}}
  window.saveOrderStatusFromDetail=L.run(async()=>{if(activeDetailOrderId)await updateStatus(activeDetailOrderId,L.value('detail-status-select'))});
  window.quickChangeStatus=L.run(updateStatus);
  window.executeConfirmedDelete=L.run(async()=>{const n=pendingDeleteId,type=pendingDeleteType;await L.api((type==='ORDER'?'/orders/':'/users/')+Number(n),'DELETE');closeConfirmModal();if(type==='ORDER')closeOrderDetail();await load();L.notice('Đã xóa dữ liệu')});
  L.form(id('customer-form'),async()=>{const n=L.value('cust-field-id'),d={};for(const k of ['username','fullname','phone','address','email'])d[k]=L.value('cust-field-'+k);const password=L.value('cust-field-password');if(password)d.password=password;await L.api('/users'+(n?'/'+Number(n):''),n?'PUT':'POST',d);closeCustomerModal();await load();L.notice('Đã lưu hồ sơ')});
  async function setupCreateOrder(){
   const toolbar=id('order-search-input')?.parentElement?.parentElement;if(!toolbar||id('create-order-button'))return;
   const trigger=document.createElement('button');trigger.id='create-order-button';trigger.type='button';trigger.className='px-space-md py-2 rounded-lg bg-primary text-on-primary font-title-sm text-title-sm whitespace-nowrap';L.text(trigger,'Tạo đơn hàng');toolbar.prepend(trigger);
   const modal=document.createElement('div');modal.id='create-order-modal';modal.className='fixed inset-0 z-50 hidden items-center justify-center bg-inverse-surface/40 backdrop-blur-sm p-4';modal.setAttribute('role','dialog');modal.setAttribute('aria-modal','true');modal.setAttribute('aria-labelledby','create-order-title');
   modal.innerHTML='<div class="stitch-create-order-panel bg-surface-container-lowest rounded-xl w-full max-w-2xl p-space-lg shadow-xl"><h2 id="create-order-title" class="font-headline-sm text-headline-sm text-on-surface">Tạo đơn hàng cho khách</h2><p class="font-body-sm text-body-sm text-on-surface-variant mt-space-xs">Chọn khách hàng, sách và số lượng.</p><form id="create-order-form" class="flex flex-col gap-space-md mt-space-lg"><label class="font-title-sm text-title-sm">Khách hàng<select id="create-order-customer" required class="stitch-order-input"></select></label><div id="create-order-items" class="flex flex-col gap-space-sm"></div><button id="create-order-add-item" type="button" class="text-primary font-title-sm text-title-sm text-left">+ Thêm sách</button><p class="font-title-md text-title-md">Tổng tiền: <span id="create-order-total">0 ₫</span></p><p id="create-order-status" role="status" class="stitch-form-status"></p><div class="flex items-center justify-end gap-space-sm"><button id="create-order-cancel" type="button" class="px-space-md py-2 rounded-lg bg-surface-container-low text-on-surface">Hủy</button><button id="create-order-submit" type="submit" class="px-space-md py-2 rounded-lg bg-primary text-on-primary font-title-sm text-title-sm">Tạo đơn hàng</button></div></form></div>';
   document.body.append(modal);let catalogue=[],saving=false;const rows=id('create-order-items'),customer=id('create-order-customer'),status=id('create-order-status');
   const money=v=>Number(v||0).toLocaleString('vi-VN')+' ₫';
   function total(){let amount=0;for(const row of rows.children){const book=catalogue.find(b=>String(b.id)===row.querySelector('select').value);amount+=Number(book?.price||0)*Number(row.querySelector('input').value||0);}L.text(id('create-order-total'),money(amount));}
   function addRow(){const row=document.createElement('div');row.className='stitch-order-line';const select=document.createElement('select');select.className='stitch-order-input';select.required=true;select.setAttribute('aria-label','Sách');select.append(new Option('Chọn sách',''),...catalogue.filter(b=>Number(b.quantity)>0).map(b=>new Option(b.title+' — '+money(b.price)+' (còn '+b.quantity+')',b.id)));const qty=document.createElement('input');qty.type='number';qty.min='1';qty.step='1';qty.value='1';qty.required=true;qty.className='stitch-order-input';qty.setAttribute('aria-label','Số lượng');const remove=document.createElement('button');remove.type='button';remove.className='text-error px-2';L.text(remove,'Xóa');L.button(remove,()=>{row.remove();total()});select.addEventListener('change',()=>{const book=catalogue.find(b=>String(b.id)===select.value);qty.max=book?.quantity||'';qty.setCustomValidity('');total()});qty.addEventListener('input',()=>{qty.setCustomValidity('');total()});row.append(select,qty,remove);rows.append(row);total();}
   function close(){if(saving)return;L.hide('create-order-modal');trigger.focus()}
   L.button(id('create-order-cancel'),close);L.button(id('create-order-add-item'),addRow);
   document.addEventListener('keydown',event=>{if(event.key==='Escape'&&!modal.classList.contains('hidden'))close()});modal.addEventListener('click',event=>{if(event.target===modal)close()});
   L.button(trigger,async()=>{trigger.disabled=true;try{const [books,users]=await Promise.all([L.api('/books'),L.api('/users')]);catalogue=books;customer.replaceChildren(new Option('Chọn khách hàng',''),...users.map(u=>new Option((u.fullname||u.username)+' ('+u.username+')',u.username)));rows.replaceChildren();addRow();L.text(status,'');L.show('create-order-modal');customer.focus()}finally{trigger.disabled=false}});
   L.form(id('create-order-form'),async()=>{if(saving)return;const quantities=new Map();for(const row of rows.children){const select=row.querySelector('select'),input=row.querySelector('input'),n=Number(input.value);if(!select.value||!Number.isInteger(n)||n<1)throw Error('Chọn sách và nhập số lượng nguyên từ 1 trở lên.');quantities.set(Number(select.value),(quantities.get(Number(select.value))||0)+n);}if(!quantities.size)throw Error('Thêm ít nhất một sách vào đơn.');for(const [bookId,qty] of quantities){const book=catalogue.find(b=>Number(b.id)===bookId);if(qty>Number(book.quantity))throw Error('Số lượng '+book.title+' vượt tồn kho ('+book.quantity+').');}
    saving=true;const controls=[...modal.querySelectorAll('button,input,select')];for(const e of controls)e.disabled=true;L.text(status,'Đang tạo đơn hàng…');try{await L.api('/orders','POST',{username:customer.value,items:[...quantities].map(([book_id,quantity])=>({book_id,quantity}))});L.hide('create-order-modal');await load();L.notice('Đã tạo đơn hàng');trigger.focus()}catch(error){L.text(status,error.message);status.dataset.success='false';throw error}finally{saving=false;for(const e of controls)e.disabled=false;}}
   );
  }
  L.run(setupCreateOrder)();

  L.run(load)();
 }
 if(C.page==='orderDetail'){
  const orderHost=$('[data-live=orderItems]'),orderTemplate=orderHost.firstElementChild.cloneNode(true);orderHost.replaceChildren();
  L.run(async()=>{const n=Number(new URLSearchParams(location.search).get('id')),o=await L.api('/orders/'+n);const users=await L.api('/users'),u=users.find(x=>x.username===o.username);let chosen=o.status;const money=v=>Number(v||0).toLocaleString('vi-VN')+' ₫';
   for(const e of $$('[data-live=orderId]'))L.text(e,'#'+o.id);for(const e of $$('[data-live=customerName]'))L.text(e,u?.fullname||o.fullname||o.username);for(const e of $$('[data-live=phone]'))L.text(e,u?.phone||'');for(const e of $$('[data-live=total]'))L.text(e,money(o.total_price));for(const e of $$('[data-live=itemCount]'))L.text(e,'Tổng kiện: '+o.items.reduce((s,b)=>s+Number(b.quantity),0)+' quyển sách');
   const host=orderHost,tpl=orderTemplate;host.replaceChildren(...o.items.map(b=>{const r=tpl.cloneNode(true),td=r.querySelectorAll('td');L.image(r.querySelector('img'),b.image);L.text(td[0]?.querySelector('[class*=font-headline]'),b.title);for(const e of td[0]?.querySelectorAll('[class*=font-body],[class*=font-label]')||[])L.text(e,'');L.text(td[1],'—');L.text(td[2],money(b.price));L.text(td[3]?.firstElementChild||td[3],'×'+b.quantity);L.text(td[4],money(b.price*b.quantity));return r}));
   for(const node of $$('[data-live=orderNotes]'))L.text(node,o.notes||'Không có ghi chú.');for(const node of $$('[data-live=paymentMethod]'))L.text(node,o.payment_method==='qr'?'QR DEMO — chưa xác nhận thanh toán':'Tiền mặt khi nhận hàng');
   L.text($('[data-live=timeline]'),'Hệ thống hiện tại chưa lưu lịch sử luân chuyển.');L.text($('[data-live=warehouse]'),'Điều phối nhiều kho chưa được hỗ trợ.');
   for(const e of $$('main span,main p')){if(!e.children.length&&/24\/10\/2024|GHTK Express|10:15 hôm nay|35\.000|Miễn phí/.test(e.textContent))L.text(e,'—');if(e.textContent.trim().startsWith('Khởi tạo:'))L.text(e,'Khởi tạo: '+o.created_at);if(e.textContent.includes('(@hoang_tran92)'))L.text(e,'Độc giả: '+(u?.fullname||o.username));}
   const addressTitle=$$('main h3').find(e=>e.textContent.trim()==='Địa Chỉ Nhận Hàng');const addressBlock=addressTitle?.parentElement?.parentElement;if(addressBlock){const ps=addressBlock.querySelectorAll('p');if(ps[0])L.text(ps[0],u?.address||'Chưa có địa chỉ trong hồ sơ');}
   const buttons=$$('#statusButtonGroup button'),save=id('saveStatusBtn'),reset=id('resetBtn'),feedback=document.createElement('p');let saving=false;feedback.id='standalone-status-feedback';feedback.className='stitch-form-status';feedback.setAttribute('role','status');id('statusButtonGroup').after(feedback);
   function mark(){const choices=statusChoices(o.status);for(const b of buttons){const target=b.lastElementChild.textContent.trim(),selected=target===chosen;b.classList.toggle('bg-primary',selected);b.classList.toggle('text-on-primary',selected);b.setAttribute('aria-pressed',String(selected));b.disabled=saving||!choices.includes(target);b.title=choices.includes(target)?target:'Không thể chuyển từ '+o.status+' sang '+target;}save.disabled=saving||chosen===o.status;save.setAttribute('aria-busy',String(saving));reset.disabled=saving;for(const badge of $$('[data-live=orderStatus]'))L.text(badge,o.status);}
   for(const b of buttons)L.button(b,()=>{chosen=b.lastElementChild.textContent.trim();mark()});mark();L.text(feedback,statusChoices(o.status).length===1?'Đơn đã kết thúc, không thể chuyển trạng thái.':'Chọn trạng thái tiếp theo rồi bấm Lưu cập nhật trạng thái.');
   L.button(reset,()=>{chosen=o.status;mark()});L.button(save,async()=>{if(saving||chosen===o.status)return;saving=true;mark();L.text(feedback,'Đang lưu trạng thái…');try{const requested=chosen;await L.api('/orders/'+n,'PUT',{status:requested});o.status=requested;feedback.dataset.success='true';L.text(feedback,'Đã lưu trạng thái: '+requested);L.notice('Đã lưu trạng thái: '+requested);}catch(error){feedback.dataset.success='false';L.text(feedback,error.message);L.notice(error.message);}finally{saving=false;mark()}});L.button(id('triggerDeleteBtn'),()=>L.show('deleteModal'));L.button(id('cancelDeleteBtn'),()=>L.hide('deleteModal'));L.button(id('confirmDeleteBtn'),async()=>{await L.api('/orders/'+n,'DELETE');location.href=C.base+'/admin/orders'});
  })();
 }
 if(C.page==='chat'){
 let selected=null,contacts=[],lastId=0,loading=false,sending=false;
 const h=$('main h3'),card=h?.closest('[class*=cursor-pointer]'),host=card?.parentElement,template=card?.cloneNode(true),stream=id('chat-messages-container');
 const status=document.createElement('p');status.className='font-body-sm text-body-sm text-on-surface-variant px-space-md py-1';status.setAttribute('role','status');stream.before(status);
 if(host)host.replaceChildren();L.text(stream,'Chọn cuộc hội thoại để xem tin nhắn.');
 const chat=(action,fields={})=>L.chatRequest(action,fields,true);
 async function messages(){
  if(!selected||loading)return;
  const targetUser=selected.username||selected.user_id;
  loading=true;
  try{
   const j=await chat('get_messages',{user_id:targetUser,last_id:lastId});
   if((selected.username||selected.user_id)!==targetUser)return;
   const follow=stream.scrollHeight-stream.scrollTop-stream.clientHeight<64;
   const formatChatTime=L.formatChatTime||(d=>{if(!d)return '';try{const t=new Date(String(d).replace(/-/g,'/'));if(isNaN(t.getTime()))return String(d);return String(t.getDate()).padStart(2,'0')+'/'+String(t.getMonth()+1).padStart(2,'0')+'/'+t.getFullYear()+' '+String(t.getHours()).padStart(2,'0')+':'+String(t.getMinutes()).padStart(2,'0');}catch(e){return String(d);}});
   for(const m of j.messages){
    const n=Number(m.id);
    if(n<=lastId)continue;
    if(!lastId)stream.replaceChildren();
    const e=document.createElement('div');
    e.className='flex flex-col '+(m.is_me?'items-end':'items-start');
    e.dataset.messageId=n;
    const bubble=document.createElement('div');
    bubble.className='max-w-[85%] p-space-sm rounded-xl font-body-sm text-body-sm shadow-sm leading-relaxed '+(m.is_me?'bg-primary text-on-primary rounded-br-none':'bg-surface-container-lowest text-on-surface rounded-bl-none');
    L.text(bubble,m.message);
    const timeSpan=document.createElement('span');
    timeSpan.className='font-label-sm text-[11px] text-on-surface-variant/80 mt-1 px-1 '+(m.is_me?'text-right':'text-left');
    L.text(timeSpan,formatChatTime(m.created_at));
    e.append(bubble,timeSpan);
    stream.append(e);
    lastId=n;
   }
   L.text(status,lastId?'':'Chưa có tin nhắn.');
   if(follow||sending)stream.scrollTop=stream.scrollHeight;
  }catch(e){
   L.text(status,e.message);
   throw e;
  }finally{
   loading=false;
  }
 }
 async function select(user){
  selected=user;
  lastId=0;
  const avatar=id('active-user-avatar');
  if(avatar)L.text(avatar,user.avatar||user.username.slice(0,2).toUpperCase());
  const fullname=id('active-user-fullname');
  if(fullname)L.text(fullname,user.fullname||user.username);
  const uname=id('active-user-username');
  if(uname)L.text(uname,'(@'+user.username+')');
  const phone=id('active-user-phone');
  if(phone)L.text(phone,user.phone||'Chưa cập nhật SĐT');
  const email=id('active-user-email');
  if(email)L.text(email,user.email||'Chưa cập nhật email');
  const address=id('active-user-address');
  if(address)L.text(address,user.address||'Chưa cập nhật địa chỉ');
  const orderCard=id('active-user-order-card');
  const orderId=id('active-user-order-id');
  const orderLink=id('active-user-order-link');
  if(orderCard&&orderId){
   if(user.order_id){
    L.text(orderId,'#DH-'+user.order_id);
    if(orderLink)orderLink.href=base+'/admin/orders?id='+user.order_id;
    orderCard.style.display='flex';
   }else{
    orderCard.style.display='none';
   }
  }
  L.text(stream,'Đang tải tin nhắn…');
  render();
  await messages();
 }
 function render(){
  if(!host||!template)return;
  const q=L.value('search-input').toLowerCase();
  const filtered=contacts.filter(u=>((u.fullname||'')+' '+(u.username||'')+' '+(u.phone||'')+' '+(u.email||'')).toLowerCase().includes(q));
  const formatChatTime=L.formatChatTime||(d=>{if(!d)return '';try{const t=new Date(String(d).replace(/-/g,'/'));if(isNaN(t.getTime()))return String(d);return String(t.getDate()).padStart(2,'0')+'/'+String(t.getMonth()+1).padStart(2,'0')+'/'+t.getFullYear()+' '+String(t.getHours()).padStart(2,'0')+':'+String(t.getMinutes()).padStart(2,'0');}catch(e){return String(d);}});
  host.replaceChildren(...filtered.map(u=>{
   const e=template.cloneNode(true);
   const av=e.querySelector('.rounded-full:not([class*=bg-secondary])');
   if(av)L.text(av,u.avatar||u.username.slice(0,2).toUpperCase());
   L.text(e.querySelector('h3'),u.fullname||u.username);
   const timeSpan=e.querySelector('h3 + span')||e.querySelector('h3 ~ span');
   if(timeSpan){
    L.text(timeSpan,formatChatTime(u.last_message));
    timeSpan.className='font-label-sm text-[11px] text-on-surface-variant whitespace-nowrap';
   }
   const snippet=e.querySelector('p');
   if(snippet)L.text(snippet,u.last_message_text||('@'+u.username));
   for(const s of e.querySelectorAll('span')){
    if(s.classList.contains('bg-surface-container')||/DH-|#/.test(s.textContent)){
     if(u.order_id){
      L.text(s,'#DH-'+u.order_id);
      s.style.display='';
     }else{
      s.style.display='none';
     }
    }else if(s.classList.contains('bg-primary')||/mới/.test(s.textContent)){
     if(u.unread_count>0){
      L.text(s,u.unread_count+' mới');
      s.style.display='';
     }else{
      s.style.display='none';
     }
    }
   }
   const isCur=selected&&(selected.username===u.username||selected.user_id===u.username);
   e.setAttribute('aria-current',isCur?'true':'false');
   e.classList.toggle('bg-surface-container-lowest',isCur);
   e.classList.toggle('shadow-[0_2px_8px_rgba(15,23,42,0.06)]',isCur);
   e.classList.toggle('bg-transparent',!isCur);
   const bar=e.querySelector('.absolute.left-0');
   if(bar)bar.classList.toggle('hidden',!isCur);
   L.button(e,()=>select(u));
   return e;
  }));
  if(!contacts.length)L.text(host,'Chưa có cuộc hội thoại.');
  L.unreadCount(contacts.reduce((n,u)=>n+Number(u.unread_count||0),0));
 }
 async function refresh(){
  await messages();
  contacts=(await chat('get_users')).users||[];
  render();
 }
 window.useQuickReply=t=>L.val('chat-textarea',t);
 window.sendMessage=L.run(async()=>{
  if(sending)return;
  if(!selected)throw Error('Chọn khách hàng trước khi gửi.');
  const message=L.value('chat-textarea');
  if(!message)throw Error('Nhập nội dung trước khi gửi.');
  sending=true;
  id('send-button').disabled=true;
  try{
   await chat('reply',{user_id:selected.username||selected.user_id,message});
   L.val('chat-textarea','');
   await messages();
   contacts=(await chat('get_users')).users||[];
   render();
   L.notice('Đã gửi phản hồi');
  }finally{
   sending=false;
   id('send-button').disabled=false;
  }
 });
 L.button(id('send-button'),window.sendMessage);
 id('chat-textarea')?.addEventListener('keydown',e=>{if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();window.sendMessage()}});
 id('search-input')?.addEventListener('input',render);
 L.run(refresh)();
 L.poll(refresh,3000);
}
})();
