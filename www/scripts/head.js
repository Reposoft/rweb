/**
 * Repos common script logic (c) Staffan Olsson http://www.repos.se
 * @version $Id$
 */

// repos: prepare
if (document.documentElement && document.documentElement.namespaceURI && document.createElementNS) {
	document.createElement = function(t) {
		return document.createElementNS(document.documentElement.namespaceURI, t);
	};
} 

/*
 * jQuery 1.1 - New Wave Javascript
 *
 * Copyright (c) 2007 John Resig (jquery.com)
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * Date: 2007-01-14 17:37:33 -0500 (Sun, 14 Jan 2007) 
 * Rev: 1073 
 */
eval(function(p,a,c,k,e,d){e=function(c){return(c<a?"":e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--){d[e(c)]=k[c]||e(c)}k=[function(e){return d[e]}];e=function(){return'\\w+'};c=1};while(c--){if(k[c]){p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c])}}return p}('k(1o 1D.6=="R"){1D.R=1D.R;u 6=l(a,c){k(1D==7)q 1v 6(a,c);a=a||11;k(6.1q(a)&&!a.1Q&&a[0]==R)q 1v 6(11)[6.C.26?"26":"2D"](a);k(1o a=="21"){u m=/^[^<]*(<.+>)[^>]*$/.2M(a);a=m?6.3f([m[1]]):6.2o(a,c)}q 7.4M(a.1g==2x&&a||(a.3R||a.G&&a!=1D&&!a.1Q&&a[0]!=R&&a[0].1Q)&&6.3G(a)||[a])};k(1o $!="R")6.31$=$;u $=6;6.C=6.8i={3R:"1.1",8j:l(){q 7.G},G:0,2g:l(1S){q 1S==R?6.3G(7):7[1S]},2p:l(a){u J=6(7);J.6i=7;q J.4M(a)},4M:l(a){7.G=0;[].1i.W(7,a);q 7},I:l(C,1t){q 6.I(7,C,1t)},4U:l(18){u 4F=-1;7.I(l(i){k(7==18)4F=i});q 4F},1x:l(1U,O,v){u 18=1U;k(1U.1g==49)k(O==R)q 6[v||"1x"](7[0],1U);H{18={};18[1U]=O}q 7.I(l(){N(u F 1y 18)6.1x(v?7.1n:7,F,6.F(7,18[F],v))})},1h:l(1U,O){q 7.1x(1U,O,"30")},2F:l(e){k(1o e=="21")q 7.3m().3h(11.8l(e));u t="";6.I(e||7,l(){6.I(7.2Q,l(){k(7.1Q!=8)t+=7.1Q!=1?7.62:6.C.2F([7])})});q t},2r:l(){u a=6.3f(1u);q 7.I(l(){u b=a[0].3T(U);7.V.3d(b,7);22(b.16)b=b.16;b.4s(7)})},3h:l(){q 7.3a(1u,U,1,l(a){7.4s(a)})},5V:l(){q 7.3a(1u,U,-1,l(a){7.3d(a,7.16)})},5e:l(){q 7.3a(1u,14,1,l(a){7.V.3d(a,7)})},5g:l(){q 7.3a(1u,14,-1,l(a){7.V.3d(a,7.2a)})},4A:l(){q 7.6i||6([])},2o:l(t){q 7.2p(6.2Z(7,l(a){q 6.2o(t,a)}))},4q:l(4z){q 7.2p(6.2Z(7,l(a){q a.3T(4z!=R?4z:U)}))},1w:l(t){q 7.2p(6.1q(t)&&6.2n(7,l(2H,4U){q t.W(2H,[4U])})||6.3v(t,7))},2f:l(t){q 7.2p(t.1g==49&&6.3v(t,7,U)||6.2n(7,l(a){k(t.1g==2x||t.3R)q 6.3u(t,a)<0;H q a!=t}))},1F:l(t){q 7.2p(6.2h(7.2g(),1o t=="21"?6(t).2g():t))},4Y:l(1l){q 1l?6.1w(1l,7).r.G>0:14},19:l(19){q 19==R?(7.G?7[0].O:1a):7.1x("O",19)},4P:l(19){q 19==R?(7.G?7[0].2z:1a):7.3m().3h(19)},3a:l(1t,1V,3D,C){u 4q=7.G>1;u a=6.3f(1t);k(3D<0)a.8n();q 7.I(l(){u 18=7;k(1V&&7.1O.1N()=="8o"&&a[0].1O.1N()=="8r")18=7.5C("28")[0]||7.4s(11.6g("28"));6.I(a,l(){C.W(18,[4q?7.3T(U):7])})})}};6.1p=6.C.1p=l(){u 1M=1u[0],a=1;k(1u.G==1){1M=7;a=0}u F;22(F=1u[a++])N(u i 1y F)1M[i]=F[i];q 1M};6.1p({8s:l(){k(6.31$)$=6.31$},1q:l(C){q C&&1o C=="l"},I:l(18,C,1t){k(18.G==R)N(u i 1y 18)C.W(18[i],1t||[i,18[i]]);H N(u i=0,5G=18.G;i<5G;i++)k(C.W(18[i],1t||[i,18[i]])===14)4o;q 18},F:l(B,O,v){k(6.1q(O))q O.3s(B);k(O.1g==3J&&v=="30")q O+"46";q O},12:{1F:l(B,c){6.I(c.3t(/\\s+/),l(i,M){k(!6.12.2T(B.12,M))B.12+=(B.12?" ":"")+M})},29:l(B,c){B.12=c?6.2n(B.12.3t(/\\s+/),l(M){q!6.12.2T(c,M)}).55(" "):""},2T:l(t,c){t=t.12||t;q t&&1v 4l("(^|\\\\s)"+c+"(\\\\s|$)").1K(t)}},40:l(e,o,f){N(u i 1y o){e.1n["1J"+i]=e.1n[i];e.1n[i]=o[i]}f.W(e,[]);N(u i 1y o)e.1n[i]=e.1n["1J"+i]},1h:l(e,p){k(p=="2e"||p=="3V"){u 1J={},3S,3e,d=["8u","8v","7s","8x"];6.I(d,l(){1J["8G"+7]=0;1J["8A"+7+"8B"]=0});6.40(e,1J,l(){k(6.1h(e,"1e")!="1X"){3S=e.8C;3e=e.8D}H{e=6(e.3T(U)).2o(":4d").5k("2V").4A().1h({4b:"1z",3U:"6k",1e:"2A",6l:"0",6m:"0"}).5d(e.V)[0];u 2P=6.1h(e.V,"3U");k(2P==""||2P=="3Z")e.V.1n.3U="6n";3S=e.6o;3e=e.6p;k(2P==""||2P=="3Z")e.V.1n.3U="3Z";e.V.38(e)}});q p=="2e"?3S:3e}q 6.30(e,p)},30:l(B,F,4Z){u J;k(F=="1d"&&6.T.1j)q 6.1x(B.1n,"1d");k(F=="4N"||F=="2O")F=6.T.1j?"3j":"2O";k(!4Z&&B.1n[F])J=B.1n[F];H k(11.3W&&11.3W.4V){k(F=="2O"||F=="3j")F="4N";F=F.1Y(/([A-Z])/g,"-$1").4T();u M=11.3W.4V(B,1a);k(M)J=M.53(F);H k(F=="1e")J="1X";H 6.40(B,{1e:"2A"},l(){u c=11.3W.4V(7,"");J=c&&c.53(F)||""})}H k(B.4R){u 54=F.1Y(/\\-(\\w)/g,l(m,c){q c.1N()});J=B.4R[F]||B.4R[54]}q J},3f:l(a){u r=[];6.I(a,l(i,1H){k(!1H)q;k(1H.1g==3J)1H=1H.6r();k(1o 1H=="21"){u s=6.2B(1H),1Z=11.6g("1Z"),2d=[];u 2r=!s.15("<1m")&&[1,"<3O>","</3O>"]||(!s.15("<8m")||!s.15("<28")||!s.15("<6u"))&&[1,"<1V>","</1V>"]||!s.15("<41")&&[2,"<1V><28>","</28></1V>"]||(!s.15("<6v")||!s.15("<6x"))&&[3,"<1V><28><41>","</41></28></1V>"]||[0,"",""];1Z.2z=2r[1]+s+2r[2];22(2r[0]--)1Z=1Z.16;k(6.T.1j){k(!s.15("<1V")&&s.15("<28")<0)2d=1Z.16&&1Z.16.2Q;H k(2r[1]=="<1V>"&&s.15("<28")<0)2d=1Z.2Q;N(u n=2d.G-1;n>=0;--n)k(2d[n].1O.1N()=="6y"&&!2d[n].2Q.G)2d[n].V.38(2d[n])}1H=1Z.2Q}k(1H.G===0)q;k(1H[0]==R)r.1i(1H);H r=6.2h(r,1H)});q r},1x:l(B,17,O){u 2m={"N":"8a","6B":"12","4N":6.T.1j?"3j":"2O",2O:6.T.1j?"3j":"2O",2z:"2z",12:"12",O:"O",2X:"2X",2V:"2V",6D:"6E",2Y:"2Y"};k(17=="1d"&&6.T.1j&&O!=R){B.83=1;q B.1w=B.1w.1Y(/4I\\([^\\)]*\\)/6F,"")+(O==1?"":"4I(1d="+O*67+")")}H k(17=="1d"&&6.T.1j)q B.1w?4m(B.1w.6H(/4I\\(1d=(.*)\\)/)[1])/67:1;k(17=="1d"&&6.T.3b&&O==1)O=0.6J;k(2m[17]){k(O!=R)B[2m[17]]=O;q B[2m[17]]}H k(O==R&&6.T.1j&&B.1O&&B.1O.1N()=="7X"&&(17=="6K"||17=="7V"))q B.7U(17).62;H k(B.6N){k(O!=R)B.6Q(17,O);q B.3B(17)}H{17=17.1Y(/-([a-z])/6R,l(z,b){q b.1N()});k(O!=R)B[17]=O;q B[17]}},2B:l(t){q t.1Y(/^\\s+|\\s+$/g,"")},3G:l(a){u r=[];k(a.1g!=2x)N(u i=0,2w=a.G;i<2w;i++)r.1i(a[i]);H r=a.3F(0);q r},3u:l(b,a){N(u i=0,2w=a.G;i<2w;i++)k(a[i]==b)q i;q-1},2h:l(2v,3M){u r=[].3F.3s(2v,0);N(u i=0,66=3M.G;i<66;i++)k(6.3u(3M[i],r)==-1)2v.1i(3M[i]);q 2v},2n:l(1L,C,44){k(1o C=="21")C=1v 4L("a","i","q "+C);u 1c=[];N(u i=0,2H=1L.G;i<2H;i++)k(!44&&C(1L[i],i)||44&&!C(1L[i],i))1c.1i(1L[i]);q 1c},2Z:l(1L,C){k(1o C=="21")C=1v 4L("a","q "+C);u 1c=[],r=[];N(u i=0,2H=1L.G;i<2H;i++){u 19=C(1L[i],i);k(19!==1a&&19!=R){k(19.1g!=2x)19=[19];1c=1c.6U(19)}}u r=1c.G?[1c[0]]:[];61:N(u i=1,5a=1c.G;i<5a;i++){N(u j=0;j<i;j++)k(1c[i]==r[j])6W 61;r.1i(1c[i])}q r}});1v l(){u b=7H.6X.4T();6.T={2E:/5O/.1K(b),3c:/3c/.1K(b),1j:/1j/.1K(b)&&!/3c/.1K(b),3b:/3b/.1K(b)&&!/(6Z|5O)/.1K(b)};6.70=!6.T.1j||11.71=="7B"};6.I({5n:"a.V",4E:"6.4E(a)",73:"6.24(a,2,\'2a\')",7z:"6.24(a,2,\'5l\')",74:"6.2I(a.V.16,a)",76:"6.2I(a.16)"},l(i,n){6.C[i]=l(a){u J=6.2Z(7,n);k(a&&1o a=="21")J=6.3v(a,J);q 7.2p(J)}});6.I({5d:"3h",78:"5V",3d:"5e",7a:"5g"},l(i,n){6.C[i]=l(){u a=1u;q 7.I(l(){N(u j=0,2w=a.G;j<2w;j++)6(a[j])[n](7)})}});6.I({5k:l(1U){6.1x(7,1U,"");7.7c(1U)},7d:l(c){6.12.1F(7,c)},7e:l(c){6.12.29(7,c)},7g:l(c){6.12[6.12.2T(7,c)?"29":"1F"](7,c)},29:l(a){k(!a||6.1w(a,[7]).r.G)7.V.38(7)},3m:l(){22(7.16)7.38(7.16)}},l(i,n){6.C[i]=l(){q 7.I(n,1u)}});6.I(["5j","5i","5f","5o"],l(i,n){6.C[n]=l(1S,C){q 7.1w(":"+n+"("+1S+")",C)}});6.I(["2e","3V"],l(i,n){6.C[n]=l(h){q h==R?(7.G?6.1h(7[0],n):1a):7.1h(n,h.1g==49?h:h+"46")}});6.1p({1l:{"":"m[2]==\'*\'||a.1O.1N()==m[2].1N()","#":"a.3B(\'3P\')==m[2]",":":{5i:"i<m[3]-0",5f:"i>m[3]-0",24:"m[3]-0==i",5j:"m[3]-0==i",2v:"i==0",2S:"i==r.G-1",5J:"i%2==0",5L:"i%2","24-3r":"6.24(a.V.16,m[3],\'2a\',a)==a","2v-3r":"6.24(a.V.16,1,\'2a\')==a","2S-3r":"6.24(a.V.7h,1,\'5l\')==a","7j-3r":"6.2I(a.V.16).G==1",5n:"a.16",3m:"!a.16",5o:"6.C.2F.W([a]).15(m[3])>=0",39:\'a.v!="1z"&&6.1h(a,"1e")!="1X"&&6.1h(a,"4b")!="1z"\',1z:\'a.v=="1z"||6.1h(a,"1e")=="1X"||6.1h(a,"4b")=="1z"\',7l:"!a.2X",2X:"a.2X",2V:"a.2V",2Y:"a.2Y||6.1x(a,\'2Y\')",2F:"a.v==\'2F\'",4d:"a.v==\'4d\'",5z:"a.v==\'5z\'",3Y:"a.v==\'3Y\'",5s:"a.v==\'5s\'",4O:"a.v==\'4O\'",5t:"a.v==\'5t\'",5u:"a.v==\'5u\'",4e:\'a.v=="4e"||a.1O=="7n"\',5w:"/5w|3O|7o|4e/i.1K(a.1O)"},".":"6.12.2T(a,m[2])","@":{"=":"z==m[4]","!=":"z!=m[4]","^=":"z&&!z.15(m[4])","$=":"z&&z.2R(z.G - m[4].G,m[4].G)==m[4]","*=":"z&&z.15(m[4])>=0","":"z",4k:l(m){q["",m[1],m[3],m[2],m[5]]},5H:"z=a[m[3]]||6.1x(a,m[3]);"},"[":"6.2o(m[2],a).G"},5E:[/^\\[ *(@)([a-2j-3x-]*) *([!*$^=]*) *(\'?"?)(.*?)\\4 *\\]/i,/^(\\[)\\s*(.*?(\\[.*?\\])?[^[]*?)\\s*\\]/,/^(:)([a-2j-3x-]*)\\("?\'?(.*?(\\(.*?\\))?[^(]*?)"?\'?\\)/i,/^([:.#]*)([a-2j-3x*-]*)/i],1P:[/^(\\/?\\.\\.)/,"a.V",/^(>|\\/)/,"6.2I(a.16)",/^(\\+)/,"6.24(a,2,\'2a\')",/^(~)/,l(a){u s=6.2I(a.V.16);q s.3F(0,6.3u(a,s))}],3v:l(1l,1L,2f){u 1J,M=[];22(1l&&1l!=1J){1J=1l;u f=6.1w(1l,1L,2f);1l=f.t.1Y(/^\\s*,\\s*/,"");M=2f?1L=f.r:6.2h(M,f.r)}q M},2o:l(t,1r){k(1o t!="21")q[t];k(1r&&!1r.1Q)1r=1a;1r=1r||11;k(!t.15("//")){1r=1r.4v;t=t.2R(2,t.G)}H k(!t.15("/")){1r=1r.4v;t=t.2R(1,t.G);k(t.15("/")>=1)t=t.2R(t.15("/"),t.G)}u J=[1r],2b=[],2S=1a;22(t&&2S!=t){u r=[];2S=t;t=6.2B(t).1Y(/^\\/\\//i,"");u 3w=14;u 1C=/^[\\/>]\\s*([a-2j-9*-]+)/i;u m=1C.2M(t);k(m){6.I(J,l(){N(u c=7.16;c;c=c.2a)k(c.1Q==1&&(c.1O==m[1].1N()||m[1]=="*"))r.1i(c)});J=r;t=6.2B(t.1Y(1C,""));3w=U}H{N(u i=0;i<6.1P.G;i+=2){u 1C=6.1P[i];u m=1C.2M(t);k(m){r=J=6.2Z(J,6.1q(6.1P[i+1])?6.1P[i+1]:l(a){q 3A(6.1P[i+1])});t=6.2B(t.1Y(1C,""));3w=U;4o}}}k(t&&!3w){k(!t.15(",")){k(J[0]==1r)J.4K();6.2h(2b,J);r=J=[1r];t=" "+t.2R(1,t.G)}H{u 32=/^([a-2j-3x-]+)(#)([a-2j-9\\\\*31-]*)/i;u m=32.2M(t);k(m){m=[0,m[2],m[3],m[1]]}H{32=/^([#.]?)([a-2j-9\\\\*31-]*)/i;m=32.2M(t)}k(m[1]=="#"&&J[J.G-1].4Q){u 3y=J[J.G-1].4Q(m[2]);J=r=3y&&(!m[3]||3y.1O==m[3].1N())?[3y]:[]}H{k(m[1]==".")u 4g=1v 4l("(^|\\\\s)"+m[2]+"(\\\\s|$)");6.I(J,l(){u 3g=m[1]!=""||m[0]==""?"*":m[2];k(7.1O.1N()=="7r"&&3g=="*")3g="2U";6.2h(r,m[1]!=""&&J.G!=1?6.4H(7,[],m[1],m[2],4g):7.5C(3g))});k(m[1]=="."&&J.G==1)r=6.2n(r,l(e){q 4g.1K(e.12)});k(m[1]=="#"&&J.G==1){u 5D=r;r=[];6.I(5D,l(){k(7.3B("3P")==m[2]){r=[7];q 14}})}J=r}t=t.1Y(32,"")}}k(t){u 19=6.1w(t,r);J=r=19.r;t=6.2B(19.t)}}k(J&&J[0]==1r)J.4K();6.2h(2b,J);q 2b},1w:l(t,r,2f){22(t&&/^[a-z[({<*:.#]/i.1K(t)){u p=6.5E,m;6.I(p,l(i,1C){m=1C.2M(t);k(m){t=t.7u(m[0].G);k(6.1l[m[1]].4k)m=6.1l[m[1]].4k(m);q 14}});k(m[1]==":"&&m[2]=="2f")r=6.1w(m[3],r,U).r;H k(m[1]=="."){u 1C=1v 4l("(^|\\\\s)"+m[2]+"(\\\\s|$)");r=6.2n(r,l(e){q 1C.1K(e.12||"")},2f)}H{u f=6.1l[m[1]];k(1o f!="21")f=6.1l[m[1]][m[2]];3A("f = l(a,i){"+(6.1l[m[1]].5H||"")+"q "+f+"}");r=6.2n(r,f,2f)}}q{r:r,t:t}},4H:l(o,r,1P,17,1C){N(u s=o.16;s;s=s.2a)k(s.1Q==1){u 1F=U;k(1P==".")1F=s.12&&1C.1K(s.12);H k(1P=="#")1F=s.3B("3P")==17;k(1F)r.1i(s);k(1P=="#"&&r.G)4o;k(s.16)6.4H(s,r,1P,17,1C)}q r},4E:l(B){u 4r=[];u M=B.V;22(M&&M!=11){4r.1i(M);M=M.V}q 4r},24:l(M,1c,3D,B){1c=1c||1;u 1S=0;N(;M;M=M[3D]){k(M.1Q==1)1S++;k(1S==1c||1c=="5J"&&1S%2==0&&1S>1&&M==B||1c=="5L"&&1S%2==1&&M==B)q M}},2I:l(n,B){u r=[];N(;n;n=n.2a){k(n.1Q==1&&(!B||n!=B))r.1i(n)}q r}});6.E={1F:l(Q,v,1I,D){k(6.T.1j&&Q.45!=R)Q=1D;k(D)1I.D=D;k(!1I.2q)1I.2q=7.2q++;k(!Q.1E)Q.1E={};u 34=Q.1E[v];k(!34){34=Q.1E[v]={};k(Q["35"+v])34[0]=Q["35"+v]}34[1I.2q]=1I;Q["35"+v]=7.5P;k(!7.1f[v])7.1f[v]=[];7.1f[v].1i(Q)},2q:1,1f:{},29:l(Q,v,1I){k(Q.1E)k(v&&v.v)4x Q.1E[v.v][v.1I.2q];H k(v&&Q.1E[v])k(1I)4x Q.1E[v][1I.2q];H N(u i 1y Q.1E[v])4x Q.1E[v][i];H N(u j 1y Q.1E)7.29(Q,j)},1R:l(v,D,Q){D=6.3G(D||[]);k(!Q){u g=7.1f[v];k(g)6.I(g,l(){6.E.1R(v,D,7)})}H k(Q["35"+v]){D.5R(7.2m({v:v,1M:Q}));u 19=Q["35"+v].W(Q,D);k(19!==14&&6.1q(Q[v]))Q[v]()}},5P:l(E){k(1o 6=="R")q 14;E=6.E.2m(E||1D.E||{});u 3I;u c=7.1E[E.v];u 1t=[].3F.3s(1u,1);1t.5R(E);N(u j 1y c){1t[0].1I=c[j];1t[0].D=c[j].D;k(c[j].W(7,1t)===14){E.2k();E.2y();3I=14}}k(6.T.1j)E.1M=E.2k=E.2y=E.1I=E.D=1a;q 3I},2m:l(E){k(!E.1M&&E.5S)E.1M=E.5S;k(E.5T==R&&E.5W!=R){u e=11.4v,b=11.7C;E.5T=E.5W+(e.5X||b.5X);E.7E=E.7F+(e.5Y||b.5Y)}k(6.T.2E&&E.1M.1Q==3){u 37=E;E=6.1p({},37);E.1M=37.1M.V;E.2k=l(){q 37.2k()};E.2y=l(){q 37.2y()}}k(!E.2k)E.2k=l(){7.3I=14};k(!E.2y)E.2y=l(){7.7J=U};q E}};6.C.1p({3L:l(v,D,C){q 7.I(l(){6.E.1F(7,v,C||D,D)})},5U:l(v,D,C){q 7.I(l(){6.E.1F(7,v,l(E){6(7).60(E);q(C||D).W(7,1u)},D)})},60:l(v,C){q 7.I(l(){6.E.29(7,v,C)})},1R:l(v,D){q 7.I(l(){6.E.1R(v,D,7)})},3p:l(){u a=1u;q 7.68(l(e){7.4B=7.4B==0?1:0;e.2k();q a[7.4B].W(7,[e])||14})},7L:l(f,g){l 47(e){u p=(e.v=="3N"?e.7M:e.7O)||e.7P;22(p&&p!=7)2N{p=p.V}2u(e){p=7};k(p==7)q 14;q(e.v=="3N"?f:g).W(7,[e])}q 7.3N(47).6a(47)},26:l(f){k(6.3K)f.W(11,[6]);H{6.2W.1i(l(){q f.W(7,[6])})}q 7}});6.1p({3K:14,2W:[],26:l(){k(!6.3K){6.3K=U;k(6.2W){6.I(6.2W,l(){7.W(11)});6.2W=1a}k(6.T.3b||6.T.3c)11.7R("6e",6.26,14)}}});1v l(){6.I(("7S,7T,2D,7W,7Y,4f,68,7Z,"+"80,81,82,3N,6a,85,3O,"+"4O,86,88,89,2L").3t(","),l(i,o){6.C[o]=l(f){q f?7.3L(o,f):7.1R(o)}});k(6.T.3b||6.T.3c)11.8c("6e",6.26,14);H k(6.T.1j){11.8d("<8e"+"8g 3P=69 8k=U "+"4y=//:><\\/2c>");u 2c=11.4Q("69");k(2c)2c.2l=l(){k(7.3z!="20")q;7.V.38(7);6.26()};2c=1a}H k(6.T.2E)6.4W=45(l(){k(11.3z=="8p"||11.3z=="20"){5r(6.4W);6.4W=1a;6.26()}},10);6.E.1F(1D,"2D",6.26)};k(6.T.1j)6(1D).5U("4f",l(){u 1f=6.E.1f;N(u v 1y 1f){u 4D=1f[v],i=4D.G;k(i&&v!=\'4f\')8E 6.E.29(4D[i-1],v);22(--i)}});6.C.1p({1G:l(P,K){u 1z=7.1w(":1z");q P?1z.23({2e:"1G",3V:"1G",1d:"1G"},P,K):1z.I(l(){7.1n.1e=7.2K?7.2K:"";k(6.1h(7,"1e")=="1X")7.1n.1e="2A"})},1B:l(P,K){u 39=7.1w(":39");q P?39.23({2e:"1B",3V:"1B",1d:"1B"},P,K):39.I(l(){7.2K=7.2K||6.1h(7,"1e");k(7.2K=="1X")7.2K="2A";7.1n.1e="1X"})},52:6.C.3p,3p:l(C,4S){u 1t=1u;q 6.1q(C)&&6.1q(4S)?7.52(C,4S):7.I(l(){6(7)[6(7).4Y(":1z")?"1G":"1B"].W(6(7),1t)})},6s:l(P,K){q 7.23({2e:"1G"},P,K)},6t:l(P,K){q 7.23({2e:"1B"},P,K)},6w:l(P,K){q 7.I(l(){u 6d=6(7).4Y(":1z")?"1G":"1B";6(7).23({2e:6d},P,K)})},6z:l(P,K){q 7.23({1d:"1G"},P,K)},6A:l(P,K){q 7.23({1d:"1B"},P,K)},6C:l(P,3q,K){q 7.23({1d:3q},P,K)},23:l(F,P,1k,K){q 7.1A(l(){7.2s=6.1p({},F);u 1m=6.P(P,1k,K);N(u p 1y F){u e=1v 6.36(7,1m,p);k(F[p].1g==3J)e.2t(e.M(),F[p]);H e[F[p]](F)}})},1A:l(v,C){k(!C){C=v;v="36"}q 7.I(l(){k(!7.1A)7.1A={};k(!7.1A[v])7.1A[v]=[];7.1A[v].1i(C);k(7.1A[v].G==1)C.W(7)})}});6.1p({P:l(P,1k,C){u 1m=P&&P.1g==6G?P:{20:C||!C&&1k||6.1q(P)&&P,27:P,1k:C&&1k||1k&&1k.1g!=4L&&1k};1m.27=(1m.27&&1m.27.1g==3J?1m.27:{6M:6O,6S:50}[1m.27])||6T;1m.1J=1m.20;1m.20=l(){6.5Z(7,"36");k(6.1q(1m.1J))1m.1J.W(7)};q 1m},1k:{},1A:{},5Z:l(B,v){v=v||"36";k(B.1A&&B.1A[v]){B.1A[v].4K();u f=B.1A[v][0];k(f)f.W(B)}},36:l(B,1b,F){u z=7;u y=B.1n;u 42=6.1h(B,"1e");y.1e="2A";y.5v="1z";z.a=l(){k(1b.3n)1b.3n.W(B,[z.2i]);k(F=="1d")6.1x(y,"1d",z.2i);H k(5c(z.2i))y[F]=5c(z.2i)+"46"};z.5h=l(){q 4m(6.1h(B,F))};z.M=l(){u r=4m(6.30(B,F));q r&&r>-7b?r:z.5h()};z.2t=l(48,3q){z.4j=(1v 5p()).5q();z.2i=48;z.a();z.43=45(l(){z.3n(48,3q)},13)};z.1G=l(){k(!B.1s)B.1s={};B.1s[F]=7.M();1b.1G=U;z.2t(0,B.1s[F]);k(F!="1d")y[F]="5m"};z.1B=l(){k(!B.1s)B.1s={};B.1s[F]=7.M();1b.1B=U;z.2t(B.1s[F],0)};z.3p=l(){k(!B.1s)B.1s={};B.1s[F]=7.M();k(42=="1X"){1b.1G=U;k(F!="1d")y[F]="5m";z.2t(0,B.1s[F])}H{1b.1B=U;z.2t(B.1s[F],0)}};z.3n=l(33,3E){u t=(1v 5p()).5q();k(t>1b.27+z.4j){5r(z.43);z.43=1a;z.2i=3E;z.a();k(B.2s)B.2s[F]=U;u 2b=U;N(u i 1y B.2s)k(B.2s[i]!==U)2b=14;k(2b){y.5v="";y.1e=42;k(6.1h(B,"1e")=="1X")y.1e="2A";k(1b.1B)y.1e="1X";k(1b.1B||1b.1G)N(u p 1y B.2s)k(p=="1d")6.1x(y,p,B.1s[p]);H y[p]=""}k(2b&&6.1q(1b.20))1b.20.W(B)}H{u n=t-7.4j;u p=n/1b.27;z.2i=1b.1k&&6.1k[1b.1k]?6.1k[1b.1k](p,n,33,(3E-33),1b.27):((-5I.7v(p*5I.7w)/2)+0.5)*(3E-33)+33;z.a()}}}});6.C.1p({7x:l(S,1W,K){7.2D(S,1W,K,1)},2D:l(S,1W,K,1T){k(6.1q(S))q 7.3L("2D",S);K=K||l(){};u v="63";k(1W)k(6.1q(1W.1g)){K=1W;1W=1a}H{1W=6.2U(1W);v="6c"}u 4u=7;6.3Q({S:S,v:v,D:1W,1T:1T,20:l(2G,Y){k(Y=="2J"||!1T&&Y=="5F")4u.1x("2z",2G.3H).4t().I(K,[2G.3H,Y,2G]);H K.W(4u,[2G.3H,Y,2G])}});q 7},7D:l(){q 6.2U(7)},4t:l(){q 7.2o("2c").I(l(){k(7.4y)6.6b(7.4y);H 6.4J(7.2F||7.7G||7.2z||"")}).4A()}});k(6.T.1j&&1o 3l=="R")3l=l(){q 1v 7K("7N.7Q")};6.I("57,5N,5M,6f,5K,5A".3t(","),l(i,o){6.C[o]=l(f){q 7.3L(o,f)}});6.1p({2g:l(S,D,K,v,1T){k(6.1q(D)){K=D;D=1a}q 6.3Q({S:S,D:D,2J:K,4p:v,1T:1T})},84:l(S,D,K,v){q 6.2g(S,D,K,v,1)},6b:l(S,K){q 6.2g(S,1a,K,"2c")},87:l(S,D,K){q 6.2g(S,D,K,"65")},8b:l(S,D,K,v){q 6.3Q({v:"6c",S:S,D:D,2J:K,4p:v})},8f:l(25){6.3X.25=25},8h:l(6j){6.1p(6.3X,6j)},3X:{1f:U,v:"63",25:0,59:"8q/x-8t-8y-8F",51:U,4C:U,D:1a},3i:{},3Q:l(s){s=6.1p({},6.3X,s);k(s.D){k(s.51&&1o s.D!="21")s.D=6.2U(s.D);k(s.v.4T()=="2g")s.S+=((s.S.15("?")>-1)?"&":"?")+s.D}k(s.1f&&!6.4X++)6.E.1R("57");u 4w=14;u L=1v 3l();L.6I(s.v,s.S,s.4C);k(s.D)L.3k("6L-6P",s.59);k(s.1T)L.3k("6V-4a-6Y",6.3i[s.S]||"72, 75 77 79 4c:4c:4c 7f");L.3k("X-7i-7k","3l");k(L.7m)L.3k("7p","7q");k(s.5x)s.5x(L);k(s.1f)6.E.1R("5A",[L,s]);u 2l=l(4i){k(L&&(L.3z==4||4i=="25")){4w=U;u Y;2N{Y=6.6h(L)&&4i!="25"?s.1T&&6.56(L,s.S)?"5F":"2J":"2L";k(Y!="2L"){u 3C;2N{3C=L.4h("58-4a")}2u(e){}k(s.1T&&3C)6.3i[s.S]=3C;u D=6.5B(L,s.4p);k(s.2J)s.2J(D,Y);k(s.1f)6.E.1R("5K",[L,s])}H 6.3o(s,L,Y)}2u(e){Y="2L";6.3o(s,L,Y,e)}k(s.1f)6.E.1R("5M",[L,s]);k(s.1f&&!--6.4X)6.E.1R("5N");k(s.20)s.20(L,Y);L.2l=l(){};L=1a}};L.2l=2l;k(s.25>0)64(l(){k(L){L.7A();k(!4w)2l("25")}},s.25);u 4G=L;2N{4G.7I(s.D)}2u(e){6.3o(s,L,1a,e)}k(!s.4C)2l();q 4G},3o:l(s,L,Y,e){k(s.2L)s.2L(L,Y,e);k(s.1f)6.E.1R("6f",[L,s,e])},4X:0,6h:l(r){2N{q!r.Y&&8w.8z=="3Y:"||(r.Y>=50&&r.Y<6q)||r.Y==5b||6.T.2E&&r.Y==R}2u(e){}q 14},56:l(L,S){2N{u 5Q=L.4h("58-4a");q L.Y==5b||5Q==6.3i[S]||6.T.2E&&L.Y==R}2u(e){}q 14},5B:l(r,v){u 4n=r.4h("7t-v");u D=!v&&4n&&4n.15("L")>=0;D=v=="L"||D?r.7y:r.3H;k(v=="2c")6.4J(D);k(v=="65")3A("D = "+D);k(v=="4P")6("<1Z>").4P(D).4t();q D},2U:l(a){u s=[];k(a.1g==2x||a.3R)6.I(a,l(){s.1i(2C(7.17)+"="+2C(7.O))});H N(u j 1y a)k(a[j].1g==2x)6.I(a[j],l(){s.1i(2C(j)+"="+2C(7))});H s.1i(2C(j)+"="+2C(a[j]));q s.55("&")},4J:l(D){k(1D.5y)1D.5y(D);H k(6.T.2E)1D.64(D,0);H 3A.3s(1D,D)}})}',62,539,'||||||jQuery|this|||||||||||||if|function|||||return||||var|type||||||elem|fn|data|event|prop|length|else|each|ret|callback|xml|cur|for|value|speed|element|undefined|url|browser|true|parentNode|apply||status|||document|className||false|indexOf|firstChild|name|obj|val|null|options|result|opacity|display|global|constructor|css|push|msie|easing|expr|opt|style|typeof|extend|isFunction|context|orig|args|arguments|new|filter|attr|in|hidden|queue|hide|re|window|events|add|show|arg|handler|old|test|elems|target|toUpperCase|nodeName|token|nodeType|trigger|num|ifModified|key|table|params|none|replace|div|complete|string|while|animate|nth|timeout|ready|duration|tbody|remove|nextSibling|done|script|tb|height|not|get|merge|now|z0|preventDefault|onreadystatechange|fix|grep|find|pushStack|guid|wrap|curAnim|custom|catch|first|al|Array|stopPropagation|innerHTML|block|trim|encodeURIComponent|load|safari|text|res|el|sibling|success|oldblock|error|exec|try|cssFloat|parPos|childNodes|substr|last|has|param|checked|readyList|disabled|selected|map|curCSS|_|re2|firstNum|handlers|on|fx|originalEvent|removeChild|visible|domManip|mozilla|opera|insertBefore|oWidth|clean|tag|append|lastModified|styleFloat|setRequestHeader|XMLHttpRequest|empty|step|handleError|toggle|to|child|call|split|inArray|multiFilter|foundToken|9_|oid|readyState|eval|getAttribute|modRes|dir|lastNum|slice|makeArray|responseText|returnValue|Number|isReady|bind|second|mouseover|select|id|ajax|jquery|oHeight|cloneNode|position|width|defaultView|ajaxSettings|file|static|swap|tr|oldDisplay|timer|inv|setInterval|px|handleHover|from|String|Modified|visibility|00|radio|button|unload|rec|getResponseHeader|isTimeout|startTime|_resort|RegExp|parseFloat|ct|break|dataType|clone|matched|appendChild|evalScripts|self|documentElement|requestDone|delete|src|deep|end|lastToggle|async|els|parents|pos|xml2|getAll|alpha|globalEval|shift|Function|setArray|float|submit|html|getElementById|currentStyle|fn2|toLowerCase|index|getComputedStyle|safariTimer|active|is|force|200|processData|_toggle|getPropertyValue|newProp|join|httpNotModified|ajaxStart|Last|contentType|rl|304|parseInt|appendTo|before|gt|after|max|lt|eq|removeAttr|previousSibling|1px|parent|contains|Date|getTime|clearInterval|password|image|reset|overflow|input|beforeSend|execScript|checkbox|ajaxSend|httpData|getElementsByTagName|tmp|parse|notmodified|ol|_prefix|Math|even|ajaxSuccess|odd|ajaxComplete|ajaxStop|webkit|handle|xmlRes|unshift|srcElement|pageX|one|prepend|clientX|scrollLeft|scrollTop|dequeue|unbind|check|nodeValue|GET|setTimeout|json|sl|100|click|__ie_init|mouseout|getScript|POST|state|DOMContentLoaded|ajaxError|createElement|httpSuccess|prevObject|settings|absolute|right|left|relative|clientHeight|clientWidth|300|toString|slideDown|slideUp|tfoot|td|slideToggle|th|TBODY|fadeIn|fadeOut|class|fadeTo|readonly|readOnly|gi|Object|match|open|9999|action|Content|slow|tagName|600|Type|setAttribute|ig|fast|400|concat|If|continue|userAgent|Since|compatible|boxModel|compatMode|Thu|next|siblings|01|children|Jan|prependTo|1970|insertAfter|10000|removeAttribute|addClass|removeClass|GMT|toggleClass|lastChild|Requested|only|With|enabled|overrideMimeType|BUTTON|textarea|Connection|close|OBJECT|Right|content|substring|cos|PI|loadIfModified|responseXML|prev|abort|CSS1Compat|body|serialize|pageY|clientY|textContent|navigator|send|cancelBubble|ActiveXObject|hover|fromElement|Microsoft|toElement|relatedTarget|XMLHTTP|removeEventListener|blur|focus|getAttributeNode|method|resize|FORM|scroll|dblclick|mousedown|mouseup|mousemove|zoom|getIfModified|change|keydown|getJSON|keypress|keyup|htmlFor|post|addEventListener|write|scr|ajaxTimeout|ipt|ajaxSetup|prototype|size|defer|createTextNode|thead|reverse|TABLE|loaded|application|TR|noConflict|www|Top|Bottom|location|Left|form|protocol|border|Width|offsetHeight|offsetWidth|do|urlencoded|padding'.split('|'),0,{})) 

/**
 * Repos fileid (c) 2006 repos.se
 * $Id$
 */
function ReposFileId(name) {
	this.name = name;
	this.get = function() {
		return this._idescape(this._urlescape(name));
	}
	this.find = function(prefix) {
		return document.getElementById(prefix + ':' + this.get());
	}
	this._idescape = function(text) {
		return text.replace(/[%\/\(\)@&]/g,'_');
	}
	this._urlescape = function(text) {
		return text.replace(/[^\w]+/g, function(sub) {
			return encodeURI(sub).toLowerCase();
		}).replace(/;/g,'%3b').replace(/#/g,'%23');
	}
}

/**
 * Styleswitcher from http://www.kelvinluck.com/article/switch-stylesheets-with-jquery/
 */
/**
* Styleswitch stylesheet switcher built on jQuery
* Under an Attribution, Share Alike License
* By Kelvin Luck ( http://www.kelvinluck.com/ )
**/

// special hack for xhtml+xml pages in firefox
$(document).ready(function() {
	initSwitch();
});

function initSwitch() {
	$('.styleswitch').click(function()
	{
		switchStylestyle(this.getAttribute("rel"));
		return false;
	});
	var c = readCookie('style');
	if (c) switchStylestyle(c);
}

function switchStylestyle(styleName)
{
	$('link[@rel*=style][@title]').each(function(i) 
	{
		this.disabled = true;
		if (this.getAttribute('title') == styleName) this.disabled = false;
	});
	createCookie('style', styleName, 365);
}

// cookie functions http://www.quirksmode.org/js/cookies.html
function createCookie(name,value,days)
{
	if (days)
	{
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+"="+value+expires+"; path=/";
}
function readCookie(name)
{	
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++)
	{
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}
function eraseCookie(name)
{
	createCookie(name,"",-1);
}
// /cookie functions

//repos: shared
/**
 * Repos shared script logic (c) Staffan Olsson http://www.repos.se
 * Static functions, loaded after prepare and jquery.
 * @version $Id$
 *
 * reportError(error) - handles any error message or exception
 */
var Repos = {

	// -------------- plugin setup --------------
	
	addScript: function(src) {
		var s = document.createElement('script');
		s.type = "text/javascript";
		s.src = src;
		document.getElementsByTagName('head')[0].appendChild(s);
	},

	// ------------ exception handling ------------
	
	/**
	 * Allows common error reporting routines, and logging errors to server.
	 * @param error String error message or Exception 
	 */
	reportError: function(error) {
		var error = Repos._errorToString(error);
		var id = Repos.generateId();
		// send to errorlog
		Repos._logError(error, id);
		// show to user
		var msg = "Repos has run into a script error:\n" + error + 
			  "\n\nThe details of this error have been logged so we can fix the issue. " +
			  "\nFeel free to contact support@repos.se about this error, ID \""+id+"\"." +
			  "\n\nBecause of the error, this page may not function properly.";
		Repos._alertError(msg);
	},
	
	/**
	 * Takes an error of any type and converts to a message String.
	 */
	_errorToString: function(error) {
		if (typeof(error)=='Error') {
			return Repos._exceptionToString(error);
		}
		return ''+error;
	},
	
	/**
	 * Converts a caught exception to an error message.
	 */
	_exceptionToString: function(exceptionInstance) {
		// if stacktraces are supported, add the info from it
		var msg = '(Exception';
		if (exceptionInstance.fileName) {
			msg += ' at ' + exceptionInstance.fileName;
		}
		if (exceptionInstance.lineNumber) {
			msg += ' row ' + exceptionInstance.lineNumber;
		}
		if (exceptionInstance.message) {
			msg += ') ' + exceptionInstance.message;
		} else {
			msg += ') ' + exceptionInstance;
		}
		return msg;
	},
	
	/**
	 * Sends an error report to the server, if possible.
	 */
	_logError: function(error, id) {
		var logurl = "/repos/errorlog/";
		var info = Repos._getBrowserInfo();
		info += '&id=' + id + '&message=' + error;
		if (typeof(Ajax) != 'undefined') {
			var report = new Ajax.Request(logurl, {method: 'post', parameters: info});
		} else {
			window.status = error; // Find out a way to send an error report anyway	
			return;
		}
	},
	
	/**
	 * Shows the error to the user, without requiring attention.
	 */
	_alertError: function(msg) {
		if (typeof(console) != 'undefined') { // FireBug console
			console.log(msg);
		} else if (typeof(window.console) != 'undefined') { // Safari 'defaults write com.apple.Safari IncludeDebugMenu 1'
			window.console.log(msg);
		} else if (typeof(Components)!='undefined' && typeof(Component.utils)!='undefined') { // Firefox console
			Components.utils.reportError(msg);
		} else { // don't throw exceptions because it disturbs the user, and repos works without javascript too
			window.status = "Due to a script error the page is not fully functional. Contact support@repos.se for info, error id: " + id;
		}
	},
	
	/**
	 * collect debug info about the user's environment
	 * @return as query string
	 */
	_getBrowserInfo: function() {
		var query,ref,page,date;
		page=window.location.href; // assuming that script errors occur in tools
		ref=window.referrer;
		query = 'url='+escape(page)+'&ref='+escape(ref)+'&os='+escape(navigator.userAgent)+'&browsername='+escape(navigator.appName)
			+'&browserversion='+escape(navigator.appVersion)+'&lang='+escape(navigator.language)+'&syslang='+escape(navigator.systemLanguage);
		return query;
	},
	
	/**
	 * Generate a random character sequence of length 8
	 */
	_generateId: function() {
		var chars = "ABCDEFGHIJKLMNOPQRSTUVWXTZ";
		var string_length = 8;
		var randomstring = '';
		for (var i=0; i<string_length; i++) {
			var rnum = Math.floor(Math.random() * chars.length);
			randomstring += chars.charAt(rnum);
		}
		return randomstring;
	}
	
}

// repos: resourceid
/**
 * Repos show version number (c) Staffan Olsson http://www.repos.se
 * @version $Id$
 */

function ReposResourceId(text) {
	this.text = text;
	this.getRelease = function() {
		if (/\/trunk\//.test(this.text)) return 'dev';
		var b = /\/branches\/[^\/\d]+(\d[^\/]+)/.exec(this.text);
		if (b) return b[1] + ' dev';
		var t = /\/tags\/[^\/\d]+(\d[\d\.]+)/.exec(this.text);
		if (t) {
			this.isTag = true;
			return t[1];
		}
		return '';
	}
	this.getRevision = function() {
		var rev = /Rev:\s(\d+)/.exec(this.text);
		if (rev) return rev[1];
		rev = /Id:\s\S+\s(\d+)/.exec(this.text);
		if (rev) return rev[1];
		return '';
	}
	this.getTextBefore = function() {
		return /(^[^\$]*)/.exec(this.text)[1];
	}
	this.getTextAfter = function() {
		return /([^\$]*$)/.exec(this.text)[1];
	}
}

// ----- marking screens -----
_getReleaseVersion = function(versionText) {
	var rid = new ReposResourceId(versionText);
	return rid.getTextBefore() + rid.getRelease() + rid.getTextAfter();
}

_getResourceVersion = function(versionText) {
	var rid = new ReposResourceId(versionText);
	var release = rid.getRelease();
	if (rid.isTag) return rid.getTextBefore() + release + rid.getTextAfter();
	return rid.getTextBefore() + release + ' ' + rid.getRevision() + rid.getTextAfter();
}

_showVersion = function() {
	try {
		$('#releaseversion').each( function() {
			this.innerHTML = _getReleaseVersion(this.innerHTML);
			this.style.display = '';
		} );
		$('#resourceversion').each( function() {
			this.innerHTML = _getResourceVersion(this.innerHTML);
			this.style.display = '';
		} );
	} catch (err) {
		Repos.reportError(err);
	}
},

$(document).ready( function() { _showVersion(); } );

// -------- done, load plugins --------

var plugins = new Array( 
'dateformat',
'details'
);

$(document).ready( function() {
	for (i=0; i<plugins.length; i++) {
		Repos.addScript('/repos/plugins/'+plugins[i]+'/'+plugins[i]+'.js');
	}
} );
