
<?php
  use Carbon\Carbon;
  use App\Category;
  Carbon::setLocale('es');
?>

<?php $__env->startSection('content'); ?>

  <div class="modal fade" id="createModal" tabindex="-1" role="dialog" data-backdrop="false">
    <div class="modal-dialog modal-login" role="document">
        <div class="modal-content">
            <div class="card card-signup card-plain">
                <div class="modal-header">
                  <div class="card-header card-header-primary text-center">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                      <i class="material-icons">clear</i>
                    </button>

                    <h4 class="card-title">Crea Nueva Categoría</h4>
                  </div>
                </div>
              <?php echo Form::open(array('route' => 'store.category','id' => 'form-new-category', 'method'=>'POST')); ?>

                <div class="modal-body">
                    
                        <div class="card-body">

                            <div class="form-group bmd-form-group">
                                <div class="input-group">
                                  <div class="input-group-prepend">
                                    <div class="input-group-text"><i class="material-icons">add</i></div>
                                  </div>
                                  <?php echo Form::text('name', null, array('id'=>'name','class' => 'form-control',
                                   'placeholder' =>'Nombre', 'required' => 'true')); ?>

                                </div>
                            </div>

                        </div>

                </div>
                <div class="modal-footer justify-content-center">
                    <button type="submit" class="btn btn-primary btn-link btn-wd btn-lg">Agregar</button>
                </div>
                <?php echo Form::close(); ?>

            </div>
        </div>
    </div>
</div>

      <div class="col-md-12">
          <div class="card">
            <div class="card-header card-header-primary card-header-icon">
              <h4 class="card-title">Listado de Categorías</h4>
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
                </div>
              </div>

              <?php endif; ?>

                  <div class="toolbar text-left">
                      <!--        Here you can write extra buttons/actions for the toolbar              -->
                      <a href="#" class="btn btn-rose btn-sm" data-toggle="modal" data-target="#createModal">Agregar Nueva Categoría</a>
                  </div>
                  <div class="material-datatables">

                  <table id="datatables" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                      <thead>
                          <tr>
                              <th>Nombre</th>
                              <th class="disabled-sorting">Acciones</th>
                          </tr>
                      </thead>
                      <tfoot>
                          <tr>
                              <th>Nombre</th>
                              <th>Acciones</th>
                          </tr>
                      </tfoot>
                      <tbody>
                      <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                      <?php
                        $value = Category::find($c->id);
                      ?>

              <!-- small modal -->
              <div class="modal fade modal-mini modal-primary" id="deleteModal-<?php echo e($c->id); ?>" tabindex="-1" role="dialog" data-backdrop="false">
                <div class="modal-dialog modal-small">
                  <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="material-icons">clear</i></button>
                      </div>
                      <div class="modal-body">
                        <p>¿Estás seguro que quieres borrar la categoria <?php echo e($c->name); ?>?, Si la borras todos aquellos productos que esten registrados en esta catergoría desaparecerán. ¿Deseas continuar?</p>
                      </div>
                    <div class="modal-footer justify-content-center">
                      <button type="button" class="btn btn-link" data-dismiss="modal">No</button>
                      <?php echo Form::open(['method' => 'POST','route' => ['destroy.category', $c->id]]); ?>

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

<div class="modal fade" id="editModal-<?php echo e($c->id); ?>" tabindex="-1" role="dialog" data-backdrop="false">
    <div class="modal-dialog modal-login" role="document">
        <div class="modal-content">
            <div class="card card-signup card-plain">
                <div class="modal-header">
                  <div class="card-header card-header-primary text-center">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                      <i class="material-icons">clear</i>
                    </button>

                    <h4 class="card-title">Editar Categoría</h4>
                  </div>
                </div>
              <?php echo Form::model($value, ['method' => 'POST','route' => ['update.category', $c->id]]); ?>

                <div class="modal-body">
                    
                        <div class="card-body">

                            <div class="form-group bmd-form-group">
                                <div class="input-group">
                                  <div class="input-group-prepend">
                                    <div class="input-group-text"><i class="material-icons">comment</i></div>
                                  </div>
                                  <?php echo Form::text('name', null, array('id'=>'name','class' => 'form-control', 'placeholder' =>'Nombre')); ?>

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


                      <tr>
                        <td><?php echo e($c->name); ?></td>
                        <td>
                          <a href="#" data-toggle="modal" data-target="#deleteModal-<?php echo e($c->id); ?>" class="btn btn-link btn-danger btn-just-icon remove">
                            <i class="material-icons">close</i>
                          </a>
                          <a href="#" data-toggle="modal" data-target="#editModal-<?php echo e($c->id); ?>" class="btn btn-link btn-primary btn-just-icon edit">
                            <i class="material-icons">edit</i>
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