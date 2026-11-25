
<?php
  use Carbon\Carbon;
  use App\Category;
  use App\Product;
  Carbon::setLocale('es');
?>

<?php $__env->startSection('content'); ?>

<div class="modal fade" id="createModal" tabindex="-1" role="dialog" data-backdrop="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="card card-signup card-plain">
                <div class="modal-header">
                  <div class="card-header card-header-primary text-center">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                      <i class="material-icons">clear</i>
                    </button>

                    <h4 class="card-title">Crea Nuevo Producto</h4>
                  </div>
                </div>
                <form id ="form-new-product" action="<?php echo e(route('store.product')); ?>" method="post" enctype="multipart/form-data">
              <?php echo e(csrf_field()); ?>

                <div class="modal-body">
                    
                        <div class="card-body">

                            <div class="form-row">

                                <div class="form-group col-md-12">
                                  <?php echo Form::text('name', null, array('id'=>'name','class' => 'form-control',
                                   'placeholder' =>'Nombre', 'required' => 'true')); ?>

                                </div>

                                <div class="form-group col-md-12">
                                  <?php echo Form::select('category_id', $categories, null, [
                                  'id'=>'categories' , 
                                  'class' => 'selectpicker',
                                  'data-toggle' => 'dropdown',
                                  'data-style' => 'select-with-transition',
                                  'required' => 'true', 
                                  'placeholder' => 'Seleccione Categoria'
                                ]); ?>

                                </div>

                                <div class="form-group col-md-12">
                                  <?php echo Form::textarea('description', null, ['id'=>'description', 'name'=>'description',
                                    'class' => 'form-control','length' => 500,'rows' => '5', 'maxLength' => '500', 
                                    'placeholder' => 'Descripción del producto (Opcional)']); ?>

                                </div>

                                <div class="form-group col-md-12">
                                  <?php echo Form::text('price', null, array('id'=>'price', 'name'=>'price', 'class' => 'form-control',
                                    'required' => 'true', 'number' => 'true', 'placeholder' => '$ Valor del Producto')); ?>

                                </div>

                                <div class="fileinput fileinput-new text-center" data-provides="fileinput">
                                   <div class="fileinput-new thumbnail img-raised">
                                  <img src="<?php echo e(asset('/assets/img/image_placeholder.jpg')); ?>">
                                   </div>
                                   <div class="fileinput-preview fileinput-exists thumbnail img-raised"></div>
                                   <div>
                                  <span class="btn btn-raised btn-round btn-default btn-file">
                                     <span class="fileinput-new">Seleccionar Imágen</span>
                                     <span class="fileinput-exists">Cambiar</span>
                                     <input id="photo" type="file" name="photo" />
                                  </span>
                                        <a href="#" class="btn btn-danger btn-round fileinput-exists" data-dismiss="fileinput">
                                        <i class="fa fa-times"></i> Remover</a>
                                   </div>
                                </div>
                                  
                            </div>

                        </div>

                </div>
                <div class="modal-footer justify-content-center">
                    <button type="submit" class="btn btn-primary btn-link btn-wd btn-lg">Agregar</button>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>

      <div class="col-md-12">
          <div class="card">
            <div class="card-header card-header-primary card-header-icon">
              <h4 class="card-title">Listado de Productos</h4>
            </div>
              <div class="card-body">

      <?php if($errors->all()): ?>

        <div class="alert alert-danger">
          <div class="container">
            <div class="alert-icon">
              <i class="material-icons">error_outline</i>
            </div>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <span aria-hidden="true"><i class="material-icons">clear</i></span>
            </button>
            <?php if($errors->has('name')): ?>
            <li><?php echo e($errors->first('name')); ?></li>
            <?php endif; ?>
            <?php if($errors->has('photo')): ?>
            <li><?php echo e($errors->first('photo')); ?></li>
            <?php endif; ?>
            <?php if($errors->has('image')): ?>
            <li><?php echo e($errors->first('image')); ?></li>
            <?php endif; ?>
            <?php if($errors->has('category_id')): ?>
            <li><?php echo e($errors->first('category_id')); ?></li>
            <?php endif; ?>
            <?php if($errors->has('price')): ?>
            <li><?php echo e($errors->first('price')); ?></li>
            <?php endif; ?>
            <?php if($errors->has('description')): ?>
            <li><?php echo e($errors->first('description')); ?></li>
            <?php endif; ?>
          </div>
        </div>

        <?php endif; ?>

                  <div class="toolbar text-left">
                      <!--        Here you can write extra buttons/actions for the toolbar              -->
                      <a href="#" class="btn btn-rose btn-sm" data-toggle="modal" data-target="#createModal">Agregar Nuevo Producto</a>
                  </div>
                  <div class="material-datatables">

                  <table id="datatables" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                      <thead>
                          <tr>
                              <th>Nombre</th>
                              <th>Valor Unidad</th>
                              <th>Categoria</th>
                              <th>Imágen</th>
                              <th class="disabled-sorting">Acciones</th>
                          </tr>
                      </thead>
                      <tfoot>
                          <tr>
                              <th>Nombre</th>
                              <th>Valor Unidad</th>
                              <th>Categoria</th>
                              <th>Imágen</th>
                              <th>Acciones</th>
                          </tr>
                      </tfoot>
                      <tbody>
                      <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                      <?php
                        $category = Category::find($p->category_id);
                        $value = Product::find($p->id);
                      ?>

              <!-- small modal -->
              <div class="modal fade modal-mini modal-primary" id="deleteModal-<?php echo e($p->id); ?>" tabindex="-1" role="dialog" data-backdrop="false">
                <div class="modal-dialog modal-small">
                  <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="material-icons">clear</i></button>
                      </div>
                      <div class="modal-body">
                        <p>¿Estás seguro que quieres borrar el producto <?php echo e($p->name); ?>?</p>
                      </div>
                    <div class="modal-footer justify-content-center">
                      <button type="button" class="btn btn-link" data-dismiss="modal">No</button>
                      <?php echo Form::open(['method' => 'POST','route' => ['destroy.product', $p->id]]); ?>

                      <button type="submit" class="btn btn-success btn-link">
                        Si
                        <div class="ripple-container"></div>
                      </button>
                      <?php echo Form::close(); ?>

                    </div>
                  </div>
                </div>
              </div>
              <!--    end small modal -->


  <div class="modal fade" id="editModal-<?php echo e($p->id); ?>" tabindex="-1" role="dialog" data-backdrop="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="card card-signup card-plain">
                <div class="modal-header">
                  <div class="card-header card-header-primary text-center">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                      <i class="material-icons">clear</i>
                    </button>

                    <h4 class="card-title">Editar Producto</h4>
                  </div>
                </div>
                
                <?php echo Form::model($value, ['method' => 'POST','route' => ['update.product', $p->id]]); ?>


                <div class="modal-body">
                    
                        <div class="card-body">

                            <div class="form-row">

                                <div class="form-group col-md-12">
                                  <?php echo Form::text('name', null, array('id'=>'name','class' => 'form-control',
                                   'placeholder' =>'Nombre', 'required' => 'true')); ?>

                                </div>

                                <div class="form-group col-md-12">
                                  <label for="category_id">Categoria</label>
                                    <?php echo Form::select('category_id', $categories, $value->category_id, [
                                  'id'=>'category_id',
                                  'name'=>'category_id',
                                  'class' =>'form-control'
                                  ]); ?>

                                </div>

                                <div class="form-group col-md-12">
                                  <?php echo Form::textarea('description', null, ['id'=>'description', 'name'=>'description',
                                    'class' => 'form-control','length' => 500,'rows' => '5', 'maxLength' => '500', 
                                    'placeholder' => 'Descripción del producto (Opcional)']); ?>

                                </div>

                                <div class="form-group col-md-12">
                                  <?php echo Form::text('price', null, array('id'=>'price', 'name'=>'price', 'class' => 'form-control',
                                    'required' => 'true', 'number' => 'true', 'placeholder' => '$ Valor del Producto')); ?>

                                </div>
                                  
                            </div>

                        </div>

                </div>
                <div class="modal-footer justify-content-center">
                    <button type="submit" class="btn btn-primary btn-link btn-wd btn-lg">Editar</button>
                </div>
                <?php echo Form::close(); ?>

            </div>
        </div>
    </div>
</div>

  <div class="modal fade" id="image-<?php echo e($p->id); ?>" tabindex="-1" role="dialog" data-backdrop="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="card card-signup card-plain">
                <div class="modal-header">
                  <div class="card-header card-header-primary text-center">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                      <i class="material-icons">clear</i>
                    </button>

                    <h4 class="card-title">Editar Imágen</h4>
                  </div>
                </div>
                
                <form id ="form-new-product" action="<?php echo e(route('update.product.image', $p->id)); ?>" method="post" enctype="multipart/form-data">
              <?php echo e(csrf_field()); ?>


                <div class="modal-body">
                    
                        <div class="card-body">

                            <div class="form-row">

                                <div class="fileinput fileinput-new text-center" data-provides="fileinput">
                                   <div class="fileinput-new thumbnail img-raised">
                                  <img src="<?php echo e(asset('photos'. $p->path)); ?>">
                                   </div>
                                   <div class="fileinput-preview fileinput-exists thumbnail img-raised"></div>
                                   <div>
                                  <span class="btn btn-raised btn-round btn-default btn-file">
                                     <span class="fileinput-new">Seleccionar Imágen</span>
                                     <span class="fileinput-exists">Cambiar</span>
                                     <input id="image" type="file" name="image" />
                                  </span>
                                        <a href="#" class="btn btn-danger btn-round fileinput-exists" data-dismiss="fileinput">
                                        <i class="fa fa-times"></i> Remover</a>
                                   </div>
                                </div>

                                  
                            </div>

                        </div>

                </div>
                <div class="modal-footer justify-content-center">
                    <button type="submit" class="btn btn-primary btn-link btn-wd btn-lg">Cambiar Imágen</button>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>

                      <tr>
                        <td><?php echo e($p->name); ?></td>
                        <td>$<?php echo e($p->price); ?></td>
                        <td><?php echo e($category->name); ?></td>
                        <td><img width="100px" class="img-thumbnail" src="<?php echo e(asset('photos'. $p->path)); ?>"></td>
                        <td>
                            <a data-toggle="modal" data-target="#editModal-<?php echo e($p->id); ?>" class="btn btn-link btn-warning btn-just-icon edit">
                              <i class="material-icons">edit</i>
                            </a>
                            <a href="#" data-toggle="modal" data-target="#image-<?php echo e($p->id); ?>" class="btn btn-link btn-info btn-just-icon remove">
                               <i class="material-icons">image</i>
                            </a>
                            <a href="#" data-toggle="modal" data-target="#deleteModal-<?php echo e($p->id); ?>" class="btn btn-link btn-danger btn-just-icon remove">
                               <i class="material-icons">close</i>
                            </a>
                        </td>
                      </tr>
                      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                  </table>
                  </div>
              </div><!-- end content-->
          </div><!--  end card  -->
      </div> <!-- end col-md-12 -->

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), array('__data', '__path')))->render(); ?>