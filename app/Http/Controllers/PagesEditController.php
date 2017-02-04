<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Page;
use Validator;

class PagesEditController extends Controller
{

    public function execute(Page $page,Request $request) {


        /*
         * Сохранение отредактированной инф-ии в бд
         */
        if($request->isMethod('post')) {


            $input = $request->except('_token');

            $validator = Validator::make($input,[

                'name'=>'required|max:255',
                'alias' => 'required|max:255|unique:pages,alias,'.$input['id'],
                'text' => 'required'

            ]);

            if($validator->fails()) {
                return redirect()
                    ->route('pagesEdit',['page'=>$input['id']])
                    ->withErrors($validator);
            }

            if($request->hasFile('images')) {          //загружается ли файл на сервер?
                $file = $request->file('images');
                $file->move(public_path().'/assets/img',$file->getClientOriginalName());
                $input['images'] = $file->getClientOriginalName();
            }
            else {
                $input['images'] = $input['old_images'];
            }

            unset($input['old_images']);

            $page->fill($input);

            if($page->update()) {
                return redirect('admin')->with('status','Страница обновлена');
            }

        }

        /*
         * Вывод инфы из бд в форму редактирования
         */
        /*$page = Page::find($id);*/     //или можем сами найти id методом find

        $old = $page->toArray();     //формируется массив с инфой по требуемому id
        if(view()->exists('admin.pages_edit')) {

            $data = [
                'title' => 'Редактирование страницы - '.$old['name'],
                'data' => $old
            ];
            return view('admin.pages_edit',$data);

        }

    }
}
