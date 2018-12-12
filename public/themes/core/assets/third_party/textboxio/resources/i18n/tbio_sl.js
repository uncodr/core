/** @license
 * Copyright (c) 2013-2017 Ephox Corp. All rights reserved.
 * This software is provided "AS IS," without a warranty of any kind.
 */
!function(){var a=function(){return{a11y:{widget:{title:"Pregledovalnik dostopnosti",running:"Preverjanje ...",issue:{counter:"Te\u017eava {0} od {1}",help:"Referenca WCAG 2.0\u200f \u2013 odpre se v novem oknu",none:"Ni bilo zaznanih te\u017eav z dostopom"},previous:"Prej\u0161nja te\u017eava",next:"Naslednja te\u017eava",repair:"Popravi te\u017eavo",available:"Popravilo na voljo",ignore:"Prezri"},image:{alttext:{empty:"Slike morajo imeti alternativen besedilni opis",filenameduplicate:"Alternativno besedilo ne sme biti enako kot datote\u010dno ime slike",set:"Vnesite alternativno besedilo:",validation:{empty:"Alternativno besedilo ne more biti prazno",filenameduplicate:"Alternativno besedilo ne more biti enako kot ime datoteke"}}},table:{caption:{empty:"Tabele morajo imeti napise",summaryduplicate:"Napis tabele in povzetek ne moreta imeti enake vrednosti",set:"Vnesite napis:",validation:{empty:"Napis ne more biti prazen",summaryduplicate:"Napis tabele ne more biti enak kot povzetek tabele"}},summary:{empty:"Kompleksne tabele morajo imeti povzetke",set:"Vnesite povzetek tabele:",validation:{empty:"Povzetek ne more biti prazen",captionduplicate:"Povzetek tabele ne more biti enak kot napis tabele"}},rowscells:{none:"Elementi tabele morajo vsebovati oznaki TR in TD"},headers:{none:"Tabele morajo imeti vsaj eno glavo celic",set:"Izberite glavo tabele:",validation:{none:"Izberite vrstico ali glavo stolpca"}},headerscope:{none:"Glave tabel je treba uporabiti v vrstici ali stolpcu",set:"Izberite obseg glave:",scope:{row:"Vrstica",col:"Stolpec",rowgroup:"Skupina vrstic",colgroup:"Skupina stolpcev"}}},heading:{nonsequential:"Glave morajo biti uporabljene po vrstnem redu. Na primer: Naslovu 1 naj sledi Naslov 2, ne Naslov 3.",paragraphmisuse:"Ta odstavek je videti kot naslov. \u010ce je naslov, izberite nivo naslova.",set:"Izberite nivo naslova:"},link:{adjacent:"Sosednje povezave z istim URL-jem morajo biti zdru\u017eene v eno povezavo"},list:{paragraphmisuse:"Izbrano besedilo je videti kot seznam. Sezname je treba oblikovati s pomo\u010djo oznake za seznam."},contrast:{smalltext:"Besedilo mora imeti kontrastno razmerje vsaj 4,5 : 1",largetext:"Veliko besedilo mora imeti kontrastno razmerje vsaj 3 : 1"},severity:{error:"Napaka",warning:"Opozorilo",info:"Informativno"}},aria:{autocorrect:{announce:"Samopopravek {0}"},label:{toolbar:"Orodna vrstica urejevalnika obogatenega besedila",editor:"Urejevalnik obogatenega besedila Textbox.io - {0}",fullscreen:"Celozaslonski na\u010din za urejevalnik obogatenega besedila Textbox.io - {0}",content:"Vsebina, ki se lahko ureja",more:"Kliknite za raz\u0161iritev ali strnitev"},help:{mac:"Pritisnite \u2303\u2325H za pomo\u010d",ctrl:"Pritisnite CTRL SHIFT H za pomo\u010d"},color:{picker:"Izbirnik barve",menu:"Meni izbirnika barve"},font:{color:"Barve besedila",highlight:"Barve za ozna\u010devanje",palette:"Paleta barv"},context:{menu:{generic:"Priro\u010dni meni"}},stepper:{input:{invalid:"Vrednost velikosti ni veljavna"}},table:{headerdescription:"Pritisnite preslednico, \u010de \u017eelite aktivirati nastavitve. Pritisnite TAB, \u010de se \u017eelite vrniti na izbirnik tabele.",cell:{border:{size:"Velikost obrobe"}}},input:{invalid:"Neveljaven vnos"},widget:{navigation:"Za premikanje uporabite pu\u0161\u010di\u010dne tipke."},image:{crop:{size:"Velikost obrezovanja je {0} slikovnih to\u010dk krat {1} slikovnih to\u010dk"}}},color:{white:"Bela",black:"\u010crna",gray:"Siva",metal:"Kovinska",smoke:"Dimasta (prosojna)",red:"Rde\u010da",darkred:"Temno rde\u010da",darkorange:"Temno oran\u017ena",orange:"Oran\u017ena",yellow:"Rumena",green:"Zelena",darkgreen:"Temno zelena",mediumseagreen:"Srednje morsko zelena",lightgreen:"Svetlo zelena",lime:"Rumeno zelena",mediumblue:"Srednje modra",navy:"Mornarsko modra",blue:"Modra",lightblue:"Svetlo modra",violet:"Vijoli\u010dna"},directionality:{rtldir:"Smer od desne proti levi",ltrdir:"Smer od leve proti desni"},parlance:{menu:"Jezikovni meni",set:"Nastavi jezik",ar:"Arab\u0161\u010dina",ca:"Katalon\u0161\u010dina",zh_cn:"Kitaj\u0161\u010dina (poenostavljena)",zh_tw:"Kitaj\u0161\u010dina (tradicionalna)",hr:"Hrva\u0161\u010dina",cs:"\u010ce\u0161\u010dina",da:"Dan\u0161\u010dina",nl:"Nizozem\u0161\u010dina",en:"Angle\u0161\u010dina",en_au:"Angle\u0161\u010dina (Avstralija)",en_ca:"Angle\u0161\u010dina (Kanada)",en_gb:"Angle\u0161\u010dina (Velika Britanija)",en_us:"Angle\u0161\u010dina (Zvezne dr\u017eave)",fa:"Perzij\u0161\u010dina",fi:"Fin\u0161\u010dina",fr:"Franco\u0161\u010dina",fr_ca:"Franco\u0161\u010dina (Kanada)",de:"Nem\u0161\u010dina",el:"Gr\u0161\u010dina",he:"Hebrej\u0161\u010dina",hu:"Mad\u017ear\u0161\u010dina",it:"Italijan\u0161\u010dina",ja:"Japon\u0161\u010dina",kk:"Kazahstan\u0161\u010dina",ko:"Korej\u0161\u010dina",no:"Norve\u0161\u010dina",pl:"Polj\u0161\u010dina",pt_br:"Portugal\u0161\u010dina (Brazilija)",pt_pt:"Portugal\u0161\u010dina (Portugalska)",ro:"Romun\u0161\u010dina",ru:"Ru\u0161\u010dina",sk:"Slova\u0161\u010dina",sl:"Sloven\u0161\u010dina",es:"\u0160pan\u0161\u010dina",es_419:"\u0160pan\u0161\u010dina (Latinska Amerika)",es_es:"\u0160pan\u0161\u010dina (\u0160panija)",sv:"\u0160ved\u0161\u010dina",tt:"Tatar\u0161\u010dina",th:"Taj\u0161\u010dina",tr:"Tur\u0161\u010dina",uk:"Ukrajin\u0161\u010dina"},taptoedit:"Tapni za urejanje",plaincode:{dialog:{title:"Ogled kode",editor:"Urejevalnik vira HTML"}},help:{dialog:{accessibility:"Krmarjenje s tipkovnico",a11ycheck:"Preverjanje dostopnosti",about:"Podrobnosti o Textbox.io",markdown:"Oblikovanje Markdown",shortcuts:"Bli\u017enjice na tipkovnici"}},spelling:{context:{more:"Ve\u010d",morelabel:"Podmeni za ve\u010d mo\u017enosti \u010drkovanja"},none:"Nobeno",menu:"\u010crkovanje jezika"},specialchar:{open:"Poseben znak",dialog:"Vstavi poseben znak",latin:"Latinica",insert:"Vstavi",punctuation:"Lo\u010dilo",currency:"Valute","extended-latin-a":"Podalj\u0161ana latinica A","extended-latin-b":"Podalj\u0161ana latinica B",arrows:"Pu\u0161\u010dice",mathematical:"Matemati\u010dno",miscellaneous:"Raznovrstno",selects:"Izbrani znaki",grid:"Posebni znaki"},insert:{"menu-button":"Meni za vstavljanje",menu:"Vstavi",link:"Povezava",image:"Slika",table:"Tabela",horizontalrule:"Vodoravno ravnilo",media:"Mediji"},media:{embed:"Vdelana koda za medije",insert:"Vstavi",placeholder:"Prilepi vdelano kodo sem."},wordcount:{open:"\u0160tevilo besed",dialog:"\u0160tevilo besed",counts:"\u0160tevilo",selection:"Izbor",document:"Dokument",characters:"Znaki",charactersnospaces:"Znaki (brez presledkov)",words:"Besede"},list:{unordered:{menu:"Mo\u017enosti neurejenega seznama",default:"Privzeto neurejen",circle:"Krog nerazvr\u0161\u010den",square:"Kvadrat nerazvr\u0161\u010den",disc:"Disk nerazvr\u0161\u010den"},ordered:{menu:"Mo\u017enosti urejenega seznama",default:"Privzeto urejen",decimal:"O\u0161tevil\u010deno razvr\u0161\u010den","upper-alpha":"Velika alfa razvr\u0161\u010dena","lower-alpha":"Mala alfa razvr\u0161\u010dena","upper-roman":"Velika rimska razvr\u0161\u010dena","lower-roman":"Mala rimska razvr\u0161\u010dena","lower-greek":"Mala gr\u0161ka razvr\u0161\u010dena"}},tag:{inline:{class:"span ({0})"},img:"slika"},block:{normal:"Obi\u010dajno",p:"Odstavek",h1:"Naslov 1",h2:"Naslov 2",h3:"Naslov 3",h4:"Naslov 4",h5:"Naslov 5",h6:"Naslov 6",div:"Div",pre:"Pre",li:"Element seznama",td:"Celica",th:"Glava celic",styles:"Meni slogov",dropdown:"Bloki",describe:"Trenutni slog {0}",menu:"Slogi",label:{inline:"Slogi v vrstici",table:"Slogi tabele",line:"Slogi vrstice",media:"Slogi medijev",list:"Slogi seznama",link:"Slogi povezovanja"}},font:{"menu-button":"Meni pisave",menu:"Pisava",face:"Tipografija",size:"Velikost pisave",coloroption:"Barva",describe:"Trenutna pisava {0}",color:"Besedilo",highlight:"Poudarek",stepper:{input:"Nastavi velikost pisave",increase:"Pove\u010daj velikost pisave",decrease:"Pomanj\u0161aj velikost pisave"}},cog:{"menu-button":"Meni nastavitev",menu:"Nastavitve",spellcheck:"\u010crkovanje",capitalisation:"Velika za\u010detnica",autocorrect:"Samopopravki",linkpreviews:"Pove\u017ei predoglede",help:"Pomo\u010d"},alignment:{toolbar:"Meni poravnave",menu:"Poravnava",left:"Poravnaj levo",center:"Poravnaj na sredino",right:"Poravnaj desno",justify:"Upravi\u010di poravnavo",describe:"Trenutna poravnava {0}"},category:{language:"Jezikovna skupina",undo:"Skupina razveljavi in ponovno uveljavi",insert:"Skupina vstavi",style:"Skupina slogov",emphasis:"Skupina oblikovanja",align:"Skupina poravnave",listindent:"Skupina seznamov in alinej",format:"Skupina pisave",tools:"Skupina orodij",table:"Skupina tabele",image:"Skupina urejanja slik"},action:{undo:"Razveljavi",redo:"Ponovno uveljavi",bold:"Krepko",italic:"Po\u0161evno",underline:"Pod\u010drtano",strikethrough:"Pre\u010drtano",subscript:"Podpisano",superscript:"Nadpisano",removeformat:"Odstrani oblikovanje",bullist:"Neurejen seznam",numlist:"Urejen seznam",indent:"Zamakni bolj",outdent:"Zamakni manj",blockquote:"Blockquote",fullscreen:"Celozaslonski na\u010din",search:"Pogovorno okno Najdi in zamenjaj",a11ycheck:"Preveri dostopnost",toggle:{fullscreen:"Izhod iz celozaslonskega na\u010dina"}},table:{menu:"Vstavi tabelo","column-header":"Glava stolpca","row-header":"Glava vrstice",float:"Plavajo\u010da poravnava",cell:{color:{border:"Barva obrobe",background:"Barva ozadja"},border:{width:"\u0160irina obrobe",stepper:{input:"Nastavi \u0161irino obrobe",increase:"Pove\u010daj \u0161irino obrobe",decrease:"Pomanj\u0161aj \u0161irino obrobe"}}},context:{row:{title:"Podmeni vrstice",menu:"Vrstica",insertabove:"Vstavi zgoraj",insertbelow:"Vstavi spodaj"},column:{title:"Podmeni stolpca",menu:"Stolpec",insertleft:"Vstavi levo",insertright:"Vstavi desno"},cell:{merge:"Spoji celice",unmerge:"Razdeli celico","split-cols":"Razdeli v stolpce","split-rows":"Razdeli v vrstice"},table:{title:"Podmeni tabele",menu:"Tabela",properties:"Lastnosti",delete:"Izbri\u0161i"},common:{delete:"Izbri\u0161i",normal:"Nastavi kot obi\u010dajno",header:"Nastavi kot glavo"},palette:{show:"Mo\u017enosti urejanja tabele so na voljo v orodni vrstici",hide:"Mo\u017enosti urejanja tabele niso ve\u010d na voljo"}},picker:{header:"Glava je nastavljena",label:"Izbirnik tabele",describepicker:"Uporabite pu\u0161\u010di\u010dne tipke, \u010de \u017eelite nastaviti velikost tabele.  Pritisnite tipko TAB, \u010de \u017eelite nastaviti glavo tabele. Pritisnite preslednico ali tipko ENTER, \u010de \u017eelite vstaviti tabelo.",rows:"{0} visoko",cols:"{0} \u0161iroko"},border:"Meja",summary:"Povzetek",dialog:"Lastnosti tabele",caption:"Naslov tabele",width:"\u0160irina",height:"Vi\u0161ina"},align:{none:"Ne poravnaj",center:"Poravnaj na sredino",left:"Poravnaj levo",right:"Poravnaj desno"},button:{ok:"V redu",cancel:"Prekli\u010di",close:"Prekli\u010di pogovorno okno"},banner:{close:"Zapri pasico"},border:{on:"Vklju\u010deno",off:"Izklju\u010deno",labels:{on:"Obroba vklju\u010dena",off:"Obroba izklju\u010dena"}},loading:{wait:"Po\u010dakajte"},toolbar:{more:"Ve\u010d",backbutton:"Nazaj","switch-code":"Preklopi na pogled z oznakami","switch-pencil":"Preklopi na pogled za oblikovanje"},link:{context:{edit:"Uredi povezavo",follow:"Odpri povezavo",ignore:"Prezri prekinjeno povezavo",remove:"Odstrani povezavo"},dialog:{aria:{update:"Posodobi povezavo",insert:"Vstavi povezavo",properties:"Lastnosti povezave",quick:"Hitre nastavitve"},autocomplete:{open:"Na voljo je seznam samodokon\u010danja povezav. Nadaljujte s pisanjem ali uporabite tipki navzgor in navzdol, da izberete predloge.",close:"Zaprt seznam samodokon\u010danja povezav",accept:"Izbran predlog povezave {0}"},edit:"Uredi",remove:"Odstrani",preview:"Predogled",update:"Posodobitev",insert:"Vstavi",tooltip:"Povezava"},properties:{dialog:{title:"Lastnosti povezave"},text:{label:"Besedilo za prikaz",placeholder:"Vpi\u0161i ali prilepi besedilo prikaza"},url:{label:"Dodaj povezavo URL ali zaznamek",placeholder:"Vstavi povezavo URL ali zaznamek",invalid:"Va\u0161a povezava URL je morda napa\u010dna"},title:{label:"Naslov",placeholder:"Vpi\u0161i ali prilepi naslov povezave"},button:{remove:"Odstrani"},target:{label:"Cilj",none:"Nobeno",blank:"Novo okno",top:"Celotna stran",self:"Isti okvir",parent:"Nadrejeni okvir"}},anchor:{top:"Zgornji del dokumenta",bottom:"Spodnji del dokumenta"}},fileupload:{title:"Vstavi slike",tablocal:"Lokalne datoteke",tabweburl:"Spletni URL",dropimages:"Spusti slike sem",chooseimage:"Izberi sliko za nalaganje",web:{url:"Spletni URL slike:"},weburlhelp:"Vnesite svoj URL, \u010de \u017eelite videti predogled slike. Pri velikih slikah lahko traja dlje, da se prika\u017eejo.",invalid1:"Ne moremo najti slike na URL-ju, ki ga uporabljate.",invalid2:"Preverite, ali ste v URL-ju naredili tipkarsko napako.",invalid3:"Prepri\u010dajte se, da je slika, do katere dostopate, javna in ni za\u0161\u010ditena z geslom ter ni na zasebnem omre\u017eju."},image:{context:{properties:"Lastnosti slike",palette:{show:"Mo\u017enosti urejanja slik so na voljo v orodni vrstici",hide:"Mo\u017enosti urejanja slik niso ve\u010d na voljo"}},dialog:{title:"Lastnosti slike",fields:{align:"Plavajo\u010da poravnava",url:"URL",urllocal:"Slika \u0161e ni shranjena",alt:"Nadomestno besedilo",width:"\u0160irina",height:"Vi\u0161ina",constrain:{label:"Omeji mere",on:"Zaklenjene mere",off:"Odklenjene mere"}}},menu:"Vstavi sliko","menu-button":"Meni za vstavljanje slike","from-url":"Spletni URL","from-camera":"Zvitek kamere",toolbar:{rotateleft:"Zavrti v levo",rotateright:"Zavrti v desno",fliphorizontal:"Obrni vodoravno",flipvertical:"Obrni navpi\u010dno",properties:"Lastnosti slike"},crop:{announce:"Vstopanje v vmesnik za obrezovanje. Pritisnite ENTER za potrditev, ESC za preklic.",cancel:"Preklic dejanj obrezovanja",begin:"Obre\u017ei sliko",apply:"Uporabi obrezovanje",handle:{nw:"Zgornja leva ro\u010dica obrezovanja",ne:"Zgornja desna ro\u010dica obrezovanja",se:"Spodnja desna ro\u010dica obrezovanja",sw:"Spodnja leva ro\u010dica obrezovanja",shade:"Maska obrezovanja"}}},units:{"amount-of-total":"{0} od {1}"},search:{menu:"Najdi in zamenjaj",field:{replace:"Polje Zamenjaj",search:"Polje Iskanje"},search:"Iskanje",previous:"Prej\u0161nja",next:"Naslednja",replace:"Zamenjaj","replace-all":"Zamenjaj vse",matchcase:"Razlikovanje velikih in malih \u010drk"},mentions:{initiated:"Ustvarjena omemba, nadaljujte s tipkanjem za vnaprej\u0161nje tipkanje",lookahead:{open:"Polje s seznamom vnaprej\u0161njega tipkanja",cancelled:"Preklicana omemba",searching:"I\u0161\u010dem rezultate",selected:"Vstavljena omemba {0}",noresults:"Ni rezultatov"}},cement:{dialog:{paste:{title:"Prilepi mo\u017enosti oblikovanja",instructions:"Izberi ohranitev ali odstranitev oblikovanja v prilepljeni vsebini.",merge:"Nadaljuj z oblikovanjem",clean:"Odstrani oblikovanje"},flash:{title:"Uvoz lokalne slike","trigger-paste":"Znova spro\u017eite lepljenje iz tipkovnice, da prilepite vsebino s slikami.",missing:'Za uvoz slik iz programa Microsoft Office potrebujete program Adobe Flash. Namestite <a href="http://get.adobe.com/flashplayer/" target="_blank">Adobe Flash Player</a>.',"press-escape":'Pritisnite <span class="ephox-polish-help-kbd">ESC</span>, da prezrete lokalne slike in nadaljujete z urejanjem.'}}},cloud:{error:{apikey:"Va\u0161 klju\u010d API ni veljaven.",domain:"Va\u0161 klju\u010d API ne podpira domene ({0}).",plan:"Presegli ste razpolo\u017eljivo \u0161tevilo prenosov za urednike po va\u0161em na\u010drtu. Za nadgraditev obi\u0161\u010dite spletno mesto."},dashboard:"Pojdi na nadzorno plo\u0161\u010do za skrbnike"},errors:{paste:{notready:'Funkcija "Uvoz besede" se ni nalo\u017eila. Po\u010dakajte in poskusite znova.',generic:"Med lepljenjem vsebine je pri\u0161lo do napake."},toolbar:{missing:{custom:{string:'Ukazi po meri morajo imeti lastnost "{0}", ki mora biti niz'}},invalid:"Konfiguracija za orodno vrstico ni veljavna ({0}). Za podrobnosti glejte konzolo."},spelling:{missing:{service:"Storitve za \u010drkovanje ni bilo mogo\u010de najti: ({0})."}},images:{edit:{needsproxy:"Za urejanje slik iz te domene je potreben proxy: ({0})",proxyerror:"Ne morem komunicirati s proxyjem za urejanje te slike. Za podrobnosti glejte konzolo.",generic:"Pri\u0161lo je do napake pri izvajanju dejanja za urejanje slike. Za podrobnosti glejte konzolo."},disallowed:{local:"Funkcija lepljenja lokalne slike je bila onemogo\u010dena. Lokalne slike so bile odstranjene iz lepljene vsebine.",dragdrop:"Funkcija povleci in spusti je bila onemogo\u010dena."},upload:{unknown:"Slike ni bilo mogo\u010de nalo\u017eiti.",invalid:"Vse datoteke niso bile obdelane \u2013 nekatere so bile neveljavne slike",failed:"Slike ni bilo mogo\u010de nalo\u017eiti: ({0}).",cors:"Povezave s storitvijo za nalaganje slik ni bilo mogo\u010de vzpostaviti. Mo\u017ene napake CORS: ({0})"},missing:{service:"Storitve za slike ni bilo mogo\u010de najti: ({0}).",flash:"Varnostne nastavitve va\u0161ega brskalnika morda prepre\u010dujejo, da bi bile slike name\u0161\u010dene."},import:{failed:"Nekatere slike niso bile uvo\u017eene.",unsupported:"Nepodprta vrsta slike.",invalid:"Slika je neveljavna."}},webview:{image:"Neposredno kopiranih slik ni mogo\u010de prilepiti."},safari:{image:"Safari ne podpira neposrednega lepljenja slik.",url:"Predlagane re\u0161itve",rtf:"Nasveti","browser-settings":"Da prilepite slike, prilagodite nastavitve brskalnika."},flash:{crashed:"Slike niso bile uvo\u017eene, saj se je program Adobe Flash zru\u0161il. To se lahko zgodi, ko posku\u0161ate prilepiti velike datoteke."},http:{400:"Napa\u010dna zahteva: {0}",401:"Nepoobla\u0161\u010deno: {0}",403:"Nedovoljeno: {0}",404:"Ni bilo najdeno: {0}",407:"Zahtevano je preverjanje prisotnosti proxyja: {0}",409:"Spor med datotekami: {0}",413:"Prevelik obseg: {0}",415:"Nepodprta vrsta medija: {0}",500:"Notranja napaka stre\u017enika: {0}",501:"Ni izvedeno: {0}"}}}},b=function(a,b){var c=a.src.indexOf("?");return a.src.indexOf(b)+b.length===c},c=function(a){for(var b=a.split("."),c=window,d=0;d<b.length&&void 0!==c&&null!==c;++d)c=c[b[d]];return c},d=function(a,b){if(a){var d="data-main",e=a.getAttribute(d);if(e){a.removeAttribute(d);var f=c(e);if("function"==typeof f)return f;console.warn("attribute on "+b+" does not reference a global method")}else console.warn("no data-main attribute found on "+b+" script tag")}},e=function(a,c){var e=d(document.currentScript,c);if(e)return e;for(var f=document.getElementsByTagName("script"),g=0;g<f.length;g++)if(b(f[g],a)){var h=d(f[g],c);if(h)return h}throw"cannot locate "+c+" script tag"},f="2.2.0",g=e("tbio_sl.js","strings for language sl");g({version:f,strings:a})}();