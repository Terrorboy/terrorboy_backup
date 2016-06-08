// 스크립트 후킹 LDD 2016-05-12
/*
fun_new: 특정 함수가 실행 되기전 실행할 함수
fun_old: 특정함수가 실행된 후 실행할 함수
parent: 함수의 위치
Tip: fun_new 함수에서 return false;를 반환 하면 fun_old를 실행 하지 않습니다.
*/
function scriptHook(fun_new, fun_old, parent) {

    if(typeof parent == 'undefined') parent = window;
    for(var i in parent) {
        if(parent[i] === fun_old) {

            parent[i] = function() {

                var Return = fun_new();
                if(Return === false) return;
                return fun_old.apply(this, arguments);
            }
            break;
        }
    }
}