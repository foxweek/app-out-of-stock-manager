<template>
  <div>
    <div class="modal fade" style="display:block" :class="{ show: title }">
      <div class="modal-dialog modal- modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-body p-0">
            <div class="card bg-secondary border-0 mb-0">
              <button type="button" class="close" @click="$parent.modal.title = ''">
                <span>×</span>
              </button>
              <div class="card-header bg-transparent pb-1">
                <div class="mt-0 mb-0 h3">Emails for:</div>
                <div class="mb-3 h5">{{ title }}</div>
              </div>
              <div class="card-body">
                <div class="row py-lg-2" v-for="(email, index) in emails">
                  <div class="col-12">{{ email }}</div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer justify-content-end">
            <div>
              <button type="button" class="btn btn-secondary" @click="$parent.modal.title = ''">Close</button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-backdrop fade" :class="{ show: title }" @click="$parent.modal.title = ''"></div>

  </div>
</template>

<script>
module.exports = {
  props: ['title', 'emails']
}
</script>