// note that dynamically included scripts are not refreshed on Ctrl+R
var p = document.createElement('p');
p.setAttribute('class','dynamicallyIncludedClass');
p.innerHTML = 'This paragraph was printed by a dynamically included script';
document.getElementsByTagName('body')[0].appendChild(p);
